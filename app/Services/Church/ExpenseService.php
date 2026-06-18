<?php

namespace App\Services\Church;

use App\Enums\ExpenseStatus;
use App\Enums\FinancialApprovalStatus;
use App\Models\Church;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ExpenseService
{
    public function __construct(
        private readonly BudgetFundingService $fundingService,
    ) {}

    public function create(Church $church, array $data, ?User $recorder = null): Expense
    {
        $data['church_id'] = $church->id;
        $data['recorded_by'] = $recorder?->id;
        $data['status'] = ExpenseStatus::Pending;
        $data['approval_status'] = FinancialApprovalStatus::Pending;

        return Expense::create($data);
    }

    public function update(Expense $expense, array $data): Expense
    {
        unset($data['church_id'], $data['status'], $data['paid_at'], $data['paid_by']);

        $expense->update($data);

        return $expense->fresh(['budget', 'recorder']);
    }

    public function delete(Expense $expense): void
    {
        if ($expense->isPaid()) {
            throw new InvalidArgumentException('Cannot delete a paid expense.');
        }

        $expense->delete();
    }

    public function markPaid(Expense $expense, User $user): Expense
    {
        if (! $expense->canBeMarkedPaid()) {
            throw new InvalidArgumentException('This expense cannot be marked as paid.');
        }

        return DB::transaction(function () use ($expense, $user) {
            if ($expense->budget_id) {
                $budget = $expense->budget;

                if ($budget) {
                    $newSpent = (float) $budget->spent_amount + (float) $expense->amount;
                    if ($newSpent > (float) $budget->total_budget) {
                        throw new InvalidArgumentException('Marking this expense as paid would exceed the budget limit.');
                    }

                    if ($budget->offeringAllocations()->exists()) {
                        $this->fundingService->deductExpenseFromAllocations($budget, (float) $expense->amount);
                    }

                    $budget->increment('spent_amount', (float) $expense->amount);
                }
            }

            $expense->update([
                'status' => ExpenseStatus::Paid,
                'paid_at' => now(),
                'paid_by' => $user->id,
            ]);

            return $expense->fresh(['budget', 'recorder', 'payer']);
        });
    }
}
