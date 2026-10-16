<?php

namespace App\Http\Controllers\Api\Pastor;

use App\Http\Requests\Church\ApproveFinancialRecordRequest;
use App\Http\Requests\Church\RejectFinancialRecordRequest;
use App\Http\Requests\Church\StoreBudgetRequest;
use App\Http\Requests\Church\StoreExpenseRequest;
use App\Http\Requests\Church\StoreOfferingRequest;
use App\Http\Requests\Church\StorePledgeRequest;
use App\Http\Requests\Church\StoreTitheRequest;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Offering;
use App\Models\PledgePayment;
use App\Models\Tithe;
use App\Services\Church\BudgetService;
use App\Services\Church\ExpenseService;
use App\Services\Church\FinanceApprovalService;
use App\Services\Church\FinanceDashboardService;
use App\Services\Church\OfferingService;
use App\Services\Church\PledgeService;
use App\Services\Church\TitheService;
use App\Support\ApiResponse;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class FinanceController extends PastorController
{
    public function __construct(
        private readonly FinanceDashboardService $financeDashboardService,
        private readonly FinanceApprovalService $financeApprovalService,
        private readonly TitheService $titheService,
        private readonly OfferingService $offeringService,
        private readonly ExpenseService $expenseService,
        private readonly PledgeService $pledgeService,
        private readonly BudgetService $budgetService,
    ) {}

    public function summary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Tithe::class);

        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $finance = $this->financeDashboardService->build(
            $this->church(),
            isset($validated['year']) ? (int) $validated['year'] : null,
            isset($validated['month']) ? (int) $validated['month'] : null,
        );

        return ApiResponse::success([
            'currency' => $this->church()->currency ?? 'TZS',
            'period' => $finance['period'],
            'summary' => $finance['summary'],
            'income_breakdown' => $finance['income_breakdown'],
            'income_trend' => $finance['income_trend'],
            'recent_transactions' => collect($finance['recent_transactions'])->map(function (array $transaction) {
                $date = $transaction['date'] ?? null;

                return [
                    'type' => $transaction['type'] ?? null,
                    'label' => $transaction['label'] ?? null,
                    'member' => $transaction['member'] ?? null,
                    'description' => $transaction['description'] ?? null,
                    'amount' => (float) ($transaction['amount'] ?? 0),
                    'date' => $date instanceof CarbonInterface ? $date->toDateString() : ($date ? (string) $date : null),
                ];
            })->values()->all(),
        ]);
    }

    public function approvals(): JsonResponse
    {
        if (! $this->pastor()->can('finance.approve')) {
            return ApiResponse::error('Forbidden', 403);
        }

        $dashboard = $this->financeApprovalService->buildDashboard($this->church()->id);
        $pending = [];

        foreach ($dashboard['pending'] as $type => $records) {
            $pending[$type] = $records->map(fn (Model $record) => $this->mapPendingRecord($type, $record))->values()->all();
        }

        return ApiResponse::success([
            'currency' => $this->church()->currency ?? 'TZS',
            'counts' => $dashboard['counts'],
            'amounts' => $dashboard['amounts'],
            'total_pending' => $dashboard['total_pending'],
            'total_pending_amount' => $dashboard['total_pending_amount'],
            'pending' => $pending,
        ]);
    }

    public function approve(ApproveFinancialRecordRequest $request): JsonResponse
    {
        try {
            $this->financeApprovalService->approve(
                $this->pastor()->church_id,
                $request->validated('type'),
                (int) $request->validated('id'),
                $this->pastor(),
                $request->validated('approval_notes')
            );
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

        return ApiResponse::success(null, 'Record approved successfully.');
    }

    public function reject(RejectFinancialRecordRequest $request): JsonResponse
    {
        try {
            $this->financeApprovalService->reject(
                $this->pastor()->church_id,
                $request->validated('type'),
                (int) $request->validated('id'),
                $this->pastor(),
                $request->validated('rejection_reason')
            );
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

        return ApiResponse::success(null, 'Record rejected successfully.');
    }

    public function storeTithe(StoreTitheRequest $request): JsonResponse
    {
        $tithe = $this->titheService->create($this->church(), $request->validated(), $this->pastor());

        return ApiResponse::success([
            'id' => $tithe->id,
            'member_id' => $tithe->member_id,
            'amount' => (float) $tithe->amount,
            'tithe_date' => $tithe->tithe_date?->toDateString(),
            'payment_method' => $tithe->payment_method?->value,
            'reference_number' => $tithe->reference_number,
            'notes' => $tithe->notes,
            'approval_status' => $tithe->approval_status?->value,
        ], 'Tithe recorded successfully and sent for approval.', 201);
    }

    public function storeOffering(StoreOfferingRequest $request): JsonResponse
    {
        $offering = $this->offeringService->create($this->church(), $request->validated(), $this->pastor());

        return ApiResponse::success([
            'id' => $offering->id,
            'member_id' => $offering->member_id,
            'amount' => (float) $offering->amount,
            'offering_date' => $offering->offering_date?->toDateString(),
            'offering_type' => $offering->offering_type?->value,
            'payment_method' => $offering->payment_method?->value,
            'reference_number' => $offering->reference_number,
            'notes' => $offering->notes,
            'approval_status' => $offering->approval_status?->value,
        ], 'Offering recorded successfully and sent for approval.', 201);
    }

    public function storeExpense(StoreExpenseRequest $request): JsonResponse
    {
        $expense = $this->expenseService->create($this->church(), $request->validated(), $this->pastor());

        return ApiResponse::success([
            'id' => $expense->id,
            'expense_name' => $expense->expense_name,
            'amount' => (float) $expense->amount,
            'expense_date' => $expense->expense_date?->toDateString(),
            'approval_status' => $expense->approval_status?->value,
        ], 'Expense submitted for approval.', 201);
    }

    public function storePledge(StorePledgeRequest $request): JsonResponse
    {
        $pledge = $this->pledgeService->create($this->church(), $request->validated(), $this->pastor());

        return ApiResponse::success([
            'id' => $pledge->id,
            'member_id' => $pledge->member_id,
            'amount' => (float) $pledge->pledge_amount,
            'status' => $pledge->status?->value,
        ], 'Pledge recorded successfully.', 201);
    }

    public function storeBudget(StoreBudgetRequest $request): JsonResponse
    {
        $budget = $this->budgetService->create($this->church(), $request->validated(), $this->pastor());

        return ApiResponse::success([
            'id' => $budget->id,
            'budget_name' => $budget->budget_name,
            'total_budget' => (float) $budget->total_budget,
            'approval_status' => $budget->approval_status?->value,
        ], 'Budget submitted for approval.', 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPendingRecord(string $type, Model $record): array
    {
        $date = match ($type) {
            'tithe' => $record->tithe_date ?? null,
            'offering' => $record->offering_date ?? null,
            'budget' => $record->start_date ?? null,
            'expense' => $record->expense_date ?? null,
            'pledge_payment' => $record->payment_date ?? null,
            default => null,
        };

        $memberName = match (true) {
            $record instanceof Tithe, $record instanceof Offering => $record->member?->full_name,
            $record instanceof PledgePayment => $record->pledge?->member?->full_name,
            default => null,
        };

        $description = match (true) {
            $record instanceof Tithe => 'Tithe'.($record->reference_number ? ' '.$record->reference_number : ''),
            $record instanceof Offering => $record->offering_type?->label() ?? 'Offering',
            $record instanceof Budget => $record->budget_name,
            $record instanceof Expense => $record->expense_name,
            $record instanceof PledgePayment => 'Pledge payment',
            default => ucfirst(str_replace('_', ' ', $type)),
        };

        $amount = $record instanceof Budget
            ? (float) $record->total_budget
            : (float) ($record->amount ?? 0);

        return [
            'type' => $type,
            'id' => $record->id,
            'amount' => $amount,
            'date' => $date instanceof CarbonInterface ? $date->toDateString() : ($date ? (string) $date : null),
            'member' => $memberName,
            'description' => $description,
            'status' => $record->approval_status?->value,
            'reference_number' => $record->reference_number ?? null,
            'notes' => $record->notes ?? $record->description ?? null,
            'created_at' => $record->created_at?->toIso8601String(),
        ];
    }
}
