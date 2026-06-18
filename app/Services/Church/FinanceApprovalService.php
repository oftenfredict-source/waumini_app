<?php

namespace App\Services\Church;

use App\Enums\FinancialApprovalStatus;
use App\Models\Budget;
use App\Models\Church;
use App\Models\Offering;
use App\Models\Expense;
use App\Models\PledgePayment;
use App\Models\Tithe;
use App\Models\User;
use App\Services\Sms\ChurchSmsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FinanceApprovalService
{
    public function __construct(
        private readonly PledgeService $pledgeService,
        private readonly ChurchSmsService $churchSmsService,
    ) {}

    /** @var array<string, array{model: class-string<Model>, label: string, icon: string, date_field: string}> */
    private array $types = [
        'tithe' => [
            'model' => Tithe::class,
            'label' => 'Tithes',
            'icon' => 'fa-money',
            'date_field' => 'tithe_date',
        ],
        'offering' => [
            'model' => Offering::class,
            'label' => 'Offerings',
            'icon' => 'fa-gift',
            'date_field' => 'offering_date',
        ],
        'budget' => [
            'model' => Budget::class,
            'label' => 'Budgets',
            'icon' => 'fa-briefcase',
            'date_field' => 'start_date',
        ],
        'expense' => [
            'model' => Expense::class,
            'label' => 'Expenses',
            'icon' => 'fa-file-text-o',
            'date_field' => 'expense_date',
        ],
        'pledge_payment' => [
            'model' => PledgePayment::class,
            'label' => 'Pledge Payments',
            'icon' => 'fa-handshake-o',
            'date_field' => 'payment_date',
        ],
    ];

    /** @return array<string, array{model: class-string<Model>, label: string, icon: string, date_field: string}> */
    public function types(): array
    {
        return $this->types;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildDashboard(int $churchId): array
    {
        $pending = $this->pendingByType($churchId);
        $counts = [];
        $amounts = [];

        foreach ($this->types as $key => $meta) {
            $records = $pending[$key];
            $counts[$key] = $records->count();
            $amounts[$key] = (float) match ($key) {
                'budget' => $records->sum('total_budget'),
                default => $records->sum('amount'),
            };
        }

        $totalPending = array_sum($counts);
        $totalPendingAmount = array_sum($amounts);

        return [
            'pending' => $pending,
            'counts' => $counts,
            'amounts' => $amounts,
            'total_pending' => $totalPending,
            'total_pending_amount' => $totalPendingAmount,
            'recent_approvals' => $this->recentApprovals($churchId, 10),
            'placeholder_tabs' => [],
        ];
    }

    /**
     * @return array<string, Collection<int, Model>>
     */
    public function pendingByType(int $churchId): array
    {
        $pending = [];

        foreach ($this->types as $key => $meta) {
            /** @var class-string<Model> $modelClass */
            $modelClass = $meta['model'];

            $query = $modelClass::forChurch($churchId)
                ->pendingApproval()
                ->latest('created_at');

            if ($key === 'pledge_payment') {
                $query->with(['pledge.member', 'recorder', 'approver']);
            } elseif ($key === 'expense') {
                $query->with(['budget', 'recorder', 'approver']);
            } elseif ($key === 'budget') {
                $query->with(['recorder', 'approver']);
            } elseif ($key === 'offering') {
                $query->with(['member', 'churchService', 'recorder', 'approver']);
            } else {
                $query->with(['member', 'recorder', 'approver']);
            }

            $pending[$key] = $query->get();
        }

        return $pending;
    }

    public function pendingSummary(int $churchId): array
    {
        $dashboard = $this->buildDashboard($churchId);

        return [
            'count' => $dashboard['total_pending'],
            'amount' => $dashboard['total_pending_amount'],
        ];
    }

    public function approve(int $churchId, string $type, int $id, User $approver, ?string $notes = null): Model
    {
        $record = $this->findPendingRecord($churchId, $type, $id);
        $record->approve($approver, $notes);

        if ($type === 'pledge_payment') {
            $this->pledgeService->applyApprovedPayment($record);
        }

        $record = $this->freshRecord($type, $record);
        $church = Church::find($churchId);

        if ($church) {
            $this->churchSmsService->sendFinanceApprovalForRecord($church, $type, $record);
        }

        return $record;
    }

    public function reject(int $churchId, string $type, int $id, User $approver, string $reason): Model
    {
        $record = $this->findPendingRecord($churchId, $type, $id);
        $record->reject($approver, $reason);

        return $this->freshRecord($type, $record);
    }

    /**
     * @param  list<array{type: string, id: int}>  $records
     */
    public function bulkApprove(int $churchId, array $records, User $approver, ?string $notes = null): int
    {
        return DB::transaction(function () use ($churchId, $records, $approver, $notes) {
            $approved = 0;

            foreach ($records as $recordData) {
                $record = $this->findPendingRecord($churchId, $recordData['type'], $recordData['id']);
                $record->approve($approver, $notes);

                if ($recordData['type'] === 'pledge_payment') {
                    $this->pledgeService->applyApprovedPayment($record);
                }

                $fresh = $this->freshRecord($recordData['type'], $record);
                $church = Church::find($churchId);

                if ($church) {
                    $this->churchSmsService->sendFinanceApprovalForRecord($church, $recordData['type'], $fresh);
                }

                $approved++;
            }

            return $approved;
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function recordDetails(int $churchId, string $type, int $id): array
    {
        $record = $this->findRecord($churchId, $type, $id);
        $meta = $this->types[$type];
        $dateField = $meta['date_field'];

        $amount = match ($type) {
            'budget' => (float) $record->total_budget,
            default => (float) $record->amount,
        };

        $data = [
            'type' => $meta['label'],
            'type_key' => $type,
            'amount' => $amount,
            'date' => $record->{$dateField}?->format('M d, Y'),
            'member_name' => match ($type) {
                'budget' => $record->budget_name,
                'expense' => $record->budget?->budget_name,
                default => $record->member?->full_name,
            },
            'payment_method' => match ($type) {
                'expense' => $record->payment_method?->label(),
                default => null,
            },
            'reference_number' => match ($type) {
                'expense' => $record->reference_number,
                default => null,
            },
            'notes' => match ($type) {
                'budget' => $record->description,
                default => $record->notes,
            },
            'recorded_by' => $record->recorder?->name ?? '—',
            'created_at' => $record->created_at?->format('M d, Y H:i'),
            'approval_status' => $record->approval_status->label(),
            'approved_by' => $record->approver?->name,
            'approved_at' => $record->approved_at?->format('M d, Y H:i'),
            'approval_notes' => $record->approval_notes,
            'rejection_reason' => $record->rejection_reason,
        ];

        if ($type === 'offering') {
            $data['offering_type'] = $record->offeringTypeLabel();
        }

        if ($type === 'pledge_payment') {
            $data['member_name'] = $record->pledge?->member?->full_name;
            $data['pledge_type'] = $record->pledge?->pledgeTypeLabel();
            $data['pledge_amount'] = $record->pledge ? (float) $record->pledge->pledge_amount : null;
            $data['pledge_paid'] = $record->pledge ? (float) $record->pledge->amount_paid : null;
        }

        if ($type === 'budget') {
            $data['budget_type'] = $record->budget_type->label();
            $data['budget_status'] = $record->status->label();
            $data['purpose'] = $record->purpose;
            $data['primary_offering_type'] = $record->primary_offering_type;
        }

        if ($type === 'expense') {
            $data['expense_category'] = $record->expense_category->label();
            $data['budget_name'] = $record->budget?->budget_name;
            $data['vendor'] = $record->vendor;
            $data['receipt_number'] = $record->receipt_number;
        }

        return $data;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function recentApprovals(int $churchId, int $limit): Collection
    {
        $recent = collect();

        foreach ($this->types as $key => $meta) {
            /** @var class-string<Model> $modelClass */
            $modelClass = $meta['model'];
            $dateField = $meta['date_field'];

            $query = $modelClass::forChurch($churchId)
                ->approved()
                ->where('approved_at', '>=', Carbon::now()->subDays(7));

            if ($key === 'pledge_payment') {
                $query->with(['pledge.member', 'approver']);
            } elseif ($key === 'expense') {
                $query->with(['budget', 'approver']);
            } elseif ($key === 'budget') {
                $query->with(['approver']);
            } else {
                $query->with(['member', 'approver']);
            }

            $items = $query->get()
                ->map(fn ($record) => [
                    'type' => $meta['label'],
                    'type_key' => $key,
                    'member_name' => match ($key) {
                        'pledge_payment' => $record->pledge?->member?->full_name ?? '—',
                        'budget' => $record->budget_name ?? '—',
                        'expense' => $record->budget?->budget_name ?? '—',
                        default => $record->member?->full_name ?? 'General / Anonymous',
                    },
                    'amount' => match ($key) {
                        'budget' => (float) $record->total_budget,
                        default => (float) $record->amount,
                    },
                    'date' => $record->{$dateField},
                    'approved_by' => $record->approver?->name ?? '—',
                    'approved_at' => $record->approved_at,
                ]);

            $recent = $recent->merge($items);
        }

        return $recent->sortByDesc('approved_at')->take($limit)->values();
    }

    private function findPendingRecord(int $churchId, string $type, int $id): Model
    {
        $record = $this->findRecord($churchId, $type, $id);

        if ($record->approval_status !== FinancialApprovalStatus::Pending) {
            throw new InvalidArgumentException('This record is no longer pending approval.');
        }

        return $record;
    }

    private function findRecord(int $churchId, string $type, int $id): Model
    {
        if (! isset($this->types[$type])) {
            throw new InvalidArgumentException('Invalid financial record type.');
        }

        /** @var class-string<Model> $modelClass */
        $modelClass = $this->types[$type]['model'];

        return $modelClass::forChurch($churchId)->whereKey($id)->firstOrFail();
    }

    private function freshRecord(string $type, Model $record): Model
    {
        if ($type === 'pledge_payment') {
            return $record->fresh(['pledge.member', 'approver', 'recorder']);
        }

        if ($type === 'expense') {
            return $record->fresh(['budget', 'approver', 'recorder']);
        }

        if ($type === 'budget') {
            return $record->fresh(['approver', 'recorder']);
        }

        return $record->fresh(['member', 'approver', 'recorder']);
    }
}
