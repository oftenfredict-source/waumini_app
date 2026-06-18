<?php

namespace App\Services\Church;

use App\Enums\ExpenseStatus;
use App\Enums\MemberStatus;
use App\Models\AttendanceRecord;
use App\Models\BereavementContribution;
use App\Models\Budget;
use App\Models\Church;
use App\Models\ChurchService;
use App\Models\Expense;
use App\Models\Leader;
use App\Models\Member;
use App\Models\MemberDependant;
use App\Models\Offering;
use App\Models\PledgePayment;
use App\Models\Tithe;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function parseDateRange(Request $request): array
    {
        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfYear();

        $end = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfYear();

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end];
    }

    /**
     * @return array<string, mixed>
     */
    public function indexSummary(Church $church, Carbon $start, Carbon $end): array
    {
        $financial = $this->financialTotals($church->id, $start, $end);

        return [
            'currency' => $church->currency ?? 'TZS',
            'total_members' => Member::forChurch($church->id)->count(),
            'active_members' => Member::forChurch($church->id)->where('status', MemberStatus::Active->value)->count(),
            'new_members_30d' => Member::forChurch($church->id)->where('created_at', '>=', now()->subDays(30))->count(),
            'financial' => $financial,
            'attendance_count' => AttendanceRecord::forChurch($church->id)
                ->whereBetween('attended_at', [$start, $end])
                ->count(),
            'active_leaders' => Leader::forChurch($church->id)->active()->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function overview(Church $church, Carbon $start, Carbon $end): array
    {
        $churchId = $church->id;
        $financial = $this->financialTotals($churchId, $start, $end);
        $offeringTypes = Offering::forChurch($churchId)
            ->approved()
            ->whereBetween('offering_date', [$start, $end])
            ->get()
            ->groupBy(fn (Offering $offering) => $offering->offeringTypeLabel())
            ->map(fn (Collection $group, string $label) => [
                'type' => $label,
                'total_amount' => (float) $group->sum('amount'),
                'count' => $group->count(),
            ])
            ->sortByDesc('total_amount')
            ->values();

        return [
            'currency' => $church->currency ?? 'TZS',
            'total_members' => Member::forChurch($churchId)->count(),
            'new_members_30d' => Member::forChurch($churchId)->where('created_at', '>=', now()->subDays(30))->count(),
            'children' => MemberDependant::forChurch($churchId)->count(),
            'financial' => $financial,
            'offering_types' => $offeringTypes,
            'top_contributors' => $this->topContributors($churchId, $start, $end, 15),
            'monthly_income' => $this->monthlyIncomeTrend($churchId, 6),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function memberSummary(Church $church): array
    {
        $members = Member::forChurch($church->id)->get(['gender', 'member_type', 'membership_type', 'status', 'date_of_birth', 'created_at']);

        $byStatus = $members->groupBy(fn (Member $m) => $m->status?->label() ?? 'Unknown')->map->count();
        $byGender = $members->groupBy(fn (Member $m) => ucfirst(strtolower((string) $m->gender)))->map->count();
        $byMemberType = $members->groupBy(fn (Member $m) => $m->member_type?->label() ?? 'Unknown')->map->count();
        $byMembershipType = $members->groupBy(fn (Member $m) => $m->membership_type?->label() ?? 'Unknown')->map->count();

        $registrations = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $registrations[] = [
                'month' => $start->format('M Y'),
                'count' => $members->filter(fn (Member $m) => $m->created_at?->between($start, $end))->count(),
            ];
        }

        return [
            'total' => $members->count(),
            'children' => MemberDependant::forChurch($church->id)->count(),
            'by_status' => $byStatus->toArray(),
            'by_gender' => $byGender->toArray(),
            'by_member_type' => $byMemberType->toArray(),
            'by_membership_type' => $byMembershipType->toArray(),
            'registrations' => $registrations,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function memberGiving(Church $church, Carbon $start, Carbon $end, ?int $memberId = null): array
    {
        $churchId = $church->id;
        $contributors = $this->memberGivingTotals($churchId, $start, $end);

        $members = Member::forChurch($churchId)->orderBy('full_name')->get(['id', 'full_name', 'member_number']);

        if ($memberId) {
            $member = Member::forChurch($churchId)->findOrFail($memberId);
            $contributors = $contributors->where('member_id', $memberId)->values();

            return [
                'currency' => $church->currency ?? 'TZS',
                'member' => $member,
                'members' => $members,
                'contributors' => $contributors,
                'transactions' => $this->memberTransactions($churchId, $memberId, $start, $end),
                'total' => (float) $contributors->sum('total'),
            ];
        }

        return [
            'currency' => $church->currency ?? 'TZS',
            'member' => null,
            'members' => $members,
            'contributors' => $contributors->sortByDesc('total')->values(),
            'transactions' => collect(),
            'total' => (float) $contributors->sum('total'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function incomeVsExpenditure(Church $church, Carbon $start, Carbon $end): array
    {
        $churchId = $church->id;
        $financial = $this->financialTotals($churchId, $start, $end);
        $monthly = $this->monthlyIncomeExpense($churchId, $start, $end);

        return [
            'currency' => $church->currency ?? 'TZS',
            'financial' => $financial,
            'monthly' => $monthly,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function offeringBreakdown(Church $church, Carbon $start, Carbon $end): array
    {
        $rows = Offering::forChurch($church->id)
            ->approved()
            ->whereBetween('offering_date', [$start, $end])
            ->get();

        $byType = $rows->groupBy(fn (Offering $o) => $o->offeringTypeLabel())
            ->map(fn (Collection $group, string $label) => [
                'type' => $label,
                'count' => $group->count(),
                'total' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        return [
            'currency' => $church->currency ?? 'TZS',
            'by_type' => $byType,
            'total' => (float) $rows->sum('amount'),
            'transaction_count' => $rows->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function budgetPerformance(Church $church, ?int $budgetId = null): array
    {
        $query = Budget::forChurch($church->id)->with(['expenses', 'lineItems'])->latest('fiscal_year');

        if ($budgetId) {
            $budgets = $query->whereKey($budgetId)->get();
        } else {
            $budgets = $query->get();
        }

        $rows = $budgets->map(fn (Budget $budget) => [
            'id' => $budget->id,
            'name' => $budget->budget_name,
            'fiscal_year' => $budget->fiscal_year,
            'total_budget' => (float) $budget->total_budget,
            'spent' => (float) $budget->spent_amount,
            'remaining' => $budget->remainingAmount(),
            'committed' => $budget->totalCommitted(),
            'utilization' => $budget->utilizationPercentage(),
            'status' => $budget->status?->label() ?? '—',
        ]);

        return [
            'currency' => $church->currency ?? 'TZS',
            'budgets' => $rows,
            'selected_budget' => $budgetId ? $budgets->first() : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function attendanceSummary(Church $church, Carbon $start, Carbon $end): array
    {
        $churchId = $church->id;
        $records = AttendanceRecord::forChurch($churchId)
            ->whereBetween('attended_at', [$start, $end])
            ->get();

        $byMonth = [];
        $cursor = $start->copy()->startOfMonth();
        while ($cursor->lte($end)) {
            $monthEnd = $cursor->copy()->endOfMonth();
            $byMonth[] = [
                'month' => $cursor->format('M Y'),
                'count' => $records->filter(fn ($r) => $r->attended_at?->between($cursor, $monthEnd))->count(),
            ];
            $cursor->addMonth();
        }

        $services = ChurchService::forChurch($churchId)
            ->whereBetween('service_date', [$start->toDateString(), $end->toDateString()])
            ->withCount('attendanceRecords')
            ->orderByDesc('service_date')
            ->limit(20)
            ->get()
            ->map(fn (ChurchService $service) => [
                'date' => $service->service_date?->format('M d, Y'),
                'title' => $service->displayTitle(),
                'attendance' => $service->attendance_records_count,
            ]);

        return [
            'total_records' => $records->count(),
            'members' => $records->whereNotNull('member_id')->count(),
            'children' => $records->whereNotNull('dependant_id')->count(),
            'by_month' => $byMonth,
            'services' => $services,
            'average_per_service' => $services->count() > 0
                ? round($services->avg('attendance'), 1)
                : 0.0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function leadership(Church $church): array
    {
        $leaders = Leader::forChurch($church->id)
            ->with('member')
            ->orderByDesc('is_active')
            ->orderBy('position')
            ->get()
            ->map(fn (Leader $leader) => [
                'name' => $leader->member?->full_name ?? '—',
                'position' => $leader->positionLabel(),
                'appointment_date' => $leader->appointment_date?->format('M d, Y') ?? '—',
                'end_date' => $leader->end_date?->format('M d, Y') ?? '—',
                'is_active' => $leader->isCurrentlyActive(),
                'phone' => $leader->member?->phone_number ?? '—',
            ]);

        return [
            'total' => $leaders->count(),
            'active' => $leaders->where('is_active', true)->count(),
            'leaders' => $leaders,
            'by_position' => $leaders->groupBy('position')->map->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function monthlyFinancial(Church $church, int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $financial = $this->financialTotals($church->id, $start, $end);

        $weeks = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $weekEnd = $cursor->copy()->addDays(6)->min($end);
            $weeks[] = [
                'label' => $cursor->format('M d').' – '.$weekEnd->format('M d'),
                'income' => $this->totalIncomeBetween($church->id, $cursor, $weekEnd),
                'expenses' => $this->paidExpensesBetween($church->id, $cursor, $weekEnd),
            ];
            $cursor->addDays(7);
        }

        return [
            'currency' => $church->currency ?? 'TZS',
            'period' => [
                'year' => $year,
                'month' => $month,
                'label' => $start->format('F Y'),
                'input' => $start->format('Y-m'),
            ],
            'financial' => $financial,
            'weeks' => $weeks,
        ];
    }

    /**
     * @return array<string, float|int>
     */
    private function financialTotals(int $churchId, Carbon $start, Carbon $end): array
    {
        $tithes = $this->approvedIncomeBetween(Tithe::class, 'tithe_date', $churchId, $start, $end);
        $offerings = $this->approvedIncomeBetween(Offering::class, 'offering_date', $churchId, $start, $end);
        $pledges = $this->approvedIncomeBetween(PledgePayment::class, 'payment_date', $churchId, $start, $end);
        $bereavements = $this->bereavementIncomeBetween($churchId, $start, $end);
        $expenses = $this->paidExpensesBetween($churchId, $start, $end);
        $income = $tithes + $offerings + $pledges + $bereavements;

        $transactionCount = Tithe::forChurch($churchId)->approved()->whereBetween('tithe_date', [$start, $end])->count()
            + Offering::forChurch($churchId)->approved()->whereBetween('offering_date', [$start, $end])->count()
            + PledgePayment::forChurch($churchId)->approved()->whereBetween('payment_date', [$start, $end])->count()
            + BereavementContribution::query()->where('has_contributed', true)
                ->whereHas('bereavementEvent', fn ($q) => $q->where('church_id', $churchId))
                ->whereBetween('contribution_date', [$start, $end])
                ->count();

        return [
            'tithes' => $tithes,
            'offerings' => $offerings,
            'pledges' => $pledges,
            'bereavements' => $bereavements,
            'expenses' => $expenses,
            'income' => $income,
            'net' => $income - $expenses,
            'transaction_count' => $transactionCount,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function topContributors(int $churchId, Carbon $start, Carbon $end, int $limit = 10): Collection
    {
        return $this->memberGivingTotals($churchId, $start, $end)
            ->sortByDesc('total')
            ->take($limit)
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function memberGivingTotals(int $churchId, Carbon $start, Carbon $end): Collection
    {
        $totals = [];

        $merge = function (Collection $rows) use (&$totals) {
            foreach ($rows as $memberId => $amount) {
                if (! $memberId) {
                    continue;
                }
                $totals[$memberId] = ($totals[$memberId] ?? 0) + (float) $amount;
            }
        };

        $merge(Tithe::forChurch($churchId)->approved()
            ->whereBetween('tithe_date', [$start, $end])
            ->selectRaw('member_id, SUM(amount) as total')
            ->groupBy('member_id')
            ->pluck('total', 'member_id'));

        $merge(Offering::forChurch($churchId)->approved()
            ->whereBetween('offering_date', [$start, $end])
            ->whereNotNull('member_id')
            ->selectRaw('member_id, SUM(amount) as total')
            ->groupBy('member_id')
            ->pluck('total', 'member_id'));

        $merge(PledgePayment::forChurch($churchId)->approved()
            ->whereBetween('payment_date', [$start, $end])
            ->with('pledge')
            ->get()
            ->filter(fn (PledgePayment $payment) => $payment->pledge?->member_id)
            ->groupBy(fn (PledgePayment $payment) => $payment->pledge->member_id)
            ->map(fn (Collection $group) => (float) $group->sum('amount')));

        $merge(BereavementContribution::query()
            ->where('has_contributed', true)
            ->whereHas('bereavementEvent', fn ($q) => $q->where('church_id', $churchId))
            ->whereBetween('contribution_date', [$start, $end])
            ->selectRaw('member_id, SUM(amount) as total')
            ->groupBy('member_id')
            ->pluck('total', 'member_id'));

        if (empty($totals)) {
            return collect();
        }

        $members = Member::forChurch($churchId)
            ->whereIn('id', array_keys($totals))
            ->get(['id', 'full_name', 'member_number'])
            ->keyBy('id');

        return collect($totals)->map(function (float $total, int $memberId) use ($members) {
            $member = $members->get($memberId);

            return [
                'member_id' => $memberId,
                'full_name' => $member?->full_name ?? 'Unknown',
                'member_number' => $member?->member_number ?? '—',
                'total' => $total,
            ];
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function memberTransactions(int $churchId, int $memberId, Carbon $start, Carbon $end): Collection
    {
        $items = collect();

        Tithe::forChurch($churchId)->approved()
            ->where('member_id', $memberId)
            ->whereBetween('tithe_date', [$start, $end])
            ->get()
            ->each(fn (Tithe $t) => $items->push([
                'date' => $t->tithe_date,
                'type' => 'Tithe',
                'amount' => (float) $t->amount,
            ]));

        Offering::forChurch($churchId)->approved()
            ->where('member_id', $memberId)
            ->whereBetween('offering_date', [$start, $end])
            ->get()
            ->each(fn (Offering $o) => $items->push([
                'date' => $o->offering_date,
                'type' => 'Offering ('.$o->offeringTypeLabel().')',
                'amount' => (float) $o->amount,
            ]));

        PledgePayment::forChurch($churchId)->approved()
            ->whereBetween('payment_date', [$start, $end])
            ->whereHas('pledge', fn ($q) => $q->where('member_id', $memberId))
            ->with('pledge')
            ->get()
            ->each(fn (PledgePayment $p) => $items->push([
                'date' => $p->payment_date,
                'type' => 'Pledge Payment',
                'amount' => (float) $p->amount,
            ]));

        BereavementContribution::query()
            ->where('member_id', $memberId)
            ->where('has_contributed', true)
            ->whereHas('bereavementEvent', fn ($q) => $q->where('church_id', $churchId))
            ->whereBetween('contribution_date', [$start, $end])
            ->get()
            ->each(fn (BereavementContribution $c) => $items->push([
                'date' => $c->contribution_date,
                'type' => 'Bereavement',
                'amount' => (float) $c->amount,
            ]));

        return $items->sortByDesc(fn ($item) => $item['date']?->timestamp ?? 0)->values();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function monthlyIncomeTrend(int $churchId, int $months): array
    {
        $trend = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $trend[] = [
                'month' => $start->format('M Y'),
                'income' => $this->totalIncomeBetween($churchId, $start, $end),
            ];
        }

        return $trend;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function monthlyIncomeExpense(int $churchId, Carbon $start, Carbon $end): array
    {
        $rows = [];
        $cursor = $start->copy()->startOfMonth();
        while ($cursor->lte($end)) {
            $monthEnd = $cursor->copy()->endOfMonth();
            $income = $this->totalIncomeBetween($churchId, $cursor, $monthEnd);
            $expenses = $this->paidExpensesBetween($churchId, $cursor, $monthEnd);
            $rows[] = [
                'month' => $cursor->format('M Y'),
                'income' => $income,
                'expenses' => $expenses,
                'net' => $income - $expenses,
            ];
            $cursor->addMonth();
        }

        return $rows;
    }

    private function totalIncomeBetween(int $churchId, Carbon $from, Carbon $to): float
    {
        return $this->approvedIncomeBetween(Tithe::class, 'tithe_date', $churchId, $from, $to)
            + $this->approvedIncomeBetween(Offering::class, 'offering_date', $churchId, $from, $to)
            + $this->approvedIncomeBetween(PledgePayment::class, 'payment_date', $churchId, $from, $to)
            + $this->bereavementIncomeBetween($churchId, $from, $to);
    }

    private function approvedIncomeBetween(
        string $modelClass,
        string $dateField,
        int $churchId,
        ?Carbon $from = null,
        ?Carbon $to = null
    ): float {
        $query = $modelClass::forChurch($churchId)->approved();

        if ($from) {
            $query->whereDate($dateField, '>=', $from);
        }

        if ($to) {
            $query->whereDate($dateField, '<=', $to);
        }

        return (float) $query->sum('amount');
    }

    private function bereavementIncomeBetween(int $churchId, ?Carbon $from = null, ?Carbon $to = null): float
    {
        $query = BereavementContribution::query()
            ->where('has_contributed', true)
            ->whereHas('bereavementEvent', fn ($q) => $q->where('church_id', $churchId));

        if ($from) {
            $query->whereDate('contribution_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('contribution_date', '<=', $to);
        }

        return (float) $query->sum('amount');
    }

    private function paidExpensesBetween(int $churchId, ?Carbon $from = null, ?Carbon $to = null): float
    {
        $query = Expense::forChurch($churchId)->where('status', ExpenseStatus::Paid->value);

        if ($from) {
            $query->whereDate('expense_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('expense_date', '<=', $to);
        }

        return (float) $query->sum('amount');
    }
}
