<?php

namespace App\Services\Church;

use App\Enums\BudgetStatus;
use App\Enums\FinancialApprovalStatus;
use App\Enums\OfferingType;
use App\Models\Budget;
use App\Models\BudgetOfferingAllocation;
use App\Models\Expense;
use App\Models\Offering;
use App\Models\PledgePayment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BudgetFundingService
{
    /**
     * @return array<string, float>
     */
    public function getAvailableIncomeAmounts(int $churchId): array
    {
        $amounts = [];

        $offerings = Offering::forChurch($churchId)
            ->approved()
            ->selectRaw('offering_type, SUM(amount) as total')
            ->groupBy('offering_type')
            ->pluck('total', 'offering_type');

        foreach ($offerings as $type => $total) {
            $key = $type instanceof OfferingType ? $type->value : (string) $type;
            $amounts[$key] = (float) $total;
        }

        $pledgeTotal = (float) PledgePayment::forChurch($churchId)->approved()->sum('amount');
        $amounts['general'] = ($amounts['general'] ?? 0) + $pledgeTotal;

        return $amounts;
    }

    /**
     * @return array<string, float>
     */
    public function getAvailableAmountsAfterAllocations(int $churchId): array
    {
        $totals = $this->getAvailableIncomeAmounts($churchId);

        $allocated = BudgetOfferingAllocation::query()
            ->whereHas('budget', fn ($q) => $q
                ->where('church_id', $churchId)
                ->where('status', BudgetStatus::Active))
            ->selectRaw('offering_type, SUM(allocated_amount) as total_allocated')
            ->groupBy('offering_type')
            ->pluck('total_allocated', 'offering_type');

        $available = [];
        foreach ($totals as $type => $total) {
            $available[$type] = max(0, $total - (float) ($allocated[$type] ?? 0));
        }

        return $available;
    }

    /**
     * @param  array<string, float>  $allocations
     * @return list<BudgetOfferingAllocation>
     */
    public function allocateFundsToBudget(Budget $budget, array $allocations): array
    {
        return DB::transaction(function () use ($budget, $allocations) {
            $created = [];
            $totalAllocated = 0;
            $available = $this->getAvailableAmountsAfterAllocations($budget->church_id);

            foreach ($allocations as $offeringType => $amount) {
                $amount = (float) $amount;
                if ($amount <= 0) {
                    continue;
                }

                $availableForType = $available[$offeringType] ?? 0;
                if ($amount > $availableForType) {
                    throw new InvalidArgumentException(
                        "Insufficient funds in {$offeringType}. Available: {$availableForType}, Requested: {$amount}"
                    );
                }

                $created[] = BudgetOfferingAllocation::create([
                    'budget_id' => $budget->id,
                    'offering_type' => $offeringType,
                    'allocated_amount' => $amount,
                    'available_amount' => $availableForType,
                    'is_primary' => $offeringType === $budget->primary_offering_type,
                    'notes' => "Allocated for {$budget->budget_name}",
                ]);

                $totalAllocated += $amount;
                $available[$offeringType] = $availableForType - $amount;
            }

            $budget->update([
                'allocated_amount' => (float) $budget->offeringAllocations()->sum('allocated_amount'),
            ]);

            return $created;
        });
    }

    public function deductExpenseFromAllocations(Budget $budget, float $expenseAmount): void
    {
        DB::transaction(function () use ($budget, $expenseAmount) {
            $remaining = $expenseAmount;

            $allocations = $budget->offeringAllocations()
                ->whereRaw('allocated_amount > used_amount')
                ->orderByDesc('is_primary')
                ->orderByDesc('allocated_amount')
                ->get();

            foreach ($allocations as $allocation) {
                if ($remaining <= 0) {
                    break;
                }

                $availableInAllocation = $allocation->remainingAmount();
                $deduction = min($remaining, $availableInAllocation);

                $allocation->increment('used_amount', $deduction);
                $remaining -= $deduction;
            }

            if ($remaining > 0) {
                throw new InvalidArgumentException(
                    'Insufficient allocated funds to cover this expense.'
                );
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function getBudgetFundingSummary(Budget $budget): array
    {
        $allocations = $budget->offeringAllocations;
        $pendingExpenses = $budget->pendingExpensesAmount();

        return [
            'total_budget' => (float) $budget->total_budget,
            'total_allocated' => (float) $allocations->sum('allocated_amount'),
            'total_used' => (float) $allocations->sum('used_amount'),
            'pending_expenses' => $pendingExpenses,
            'remaining_allocated' => max(0, (float) $allocations->sum('allocated_amount') - (float) $allocations->sum('used_amount') - $pendingExpenses),
            'funding_percentage' => $budget->fundingPercentage(),
            'is_fully_funded' => $budget->isFullyFunded(),
            'breakdown' => $allocations->map(fn (BudgetOfferingAllocation $a) => [
                'offering_type' => $a->offering_type,
                'allocated' => (float) $a->allocated_amount,
                'used' => (float) $a->used_amount,
                'remaining' => $a->remainingAmount(),
                'is_primary' => $a->is_primary,
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function offeringTypeLabels(): array
    {
        $labels = [];
        foreach (OfferingType::cases() as $type) {
            $labels[$type->value] = $type->label();
        }
        $labels['general'] = 'General (incl. pledge payments)';

        return $labels;
    }
}
