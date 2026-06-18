<?php

namespace App\Services\Church;

use App\Enums\BudgetStatus;
use App\Enums\FinancialApprovalStatus;
use App\Models\Budget;
use App\Models\BudgetLineItem;
use App\Models\Church;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    public function __construct(
        private readonly BudgetFundingService $fundingService,
    ) {}

    public function create(Church $church, array $data, ?User $recorder = null): Budget
    {
        return DB::transaction(function () use ($church, $data, $recorder) {
            $lineItems = $data['line_items'] ?? [];
            $allocations = $data['funding_allocations'] ?? [];
            unset($data['line_items'], $data['funding_allocations']);

            $data['church_id'] = $church->id;
            $data['recorded_by'] = $recorder?->id;
            $data['allocated_amount'] = 0;
            $data['spent_amount'] = 0;
            $data['status'] = $data['status'] ?? BudgetStatus::Active;
            $data['approval_status'] = FinancialApprovalStatus::Pending;

            $budget = Budget::create($data);

            $this->syncLineItems($budget, $lineItems);

            if (! empty($allocations)) {
                $this->fundingService->allocateFundsToBudget($budget, $allocations);
            }

            return $budget->fresh(['lineItems', 'offeringAllocations']);
        });
    }

    public function update(Budget $budget, array $data): Budget
    {
        return DB::transaction(function () use ($budget, $data) {
            $lineItems = $data['line_items'] ?? null;
            unset($data['line_items'], $data['funding_allocations'], $data['allocated_amount'], $data['spent_amount'], $data['church_id']);

            $budget->update($data);

            if ($lineItems !== null) {
                $this->syncLineItems($budget, $lineItems);
            }

            return $budget->fresh(['lineItems', 'offeringAllocations']);
        });
    }

    public function delete(Budget $budget): void
    {
        if ($budget->expenses()->exists()) {
            throw new \InvalidArgumentException('Cannot delete a budget that has expenses.');
        }

        $budget->delete();
    }

    public function allocateFunds(Budget $budget, array $allocations): Budget
    {
        $this->fundingService->allocateFundsToBudget($budget, $allocations);

        return $budget->fresh(['offeringAllocations']);
    }

  /**
     * @param  list<array{item_name: string, amount: float|int|string, responsible_person: string, notes?: ?string}>  $lineItems
     */
    private function syncLineItems(Budget $budget, array $lineItems): void
    {
        $budget->lineItems()->delete();

        foreach ($lineItems as $index => $item) {
            if (empty($item['item_name'])) {
                continue;
            }

            BudgetLineItem::create([
                'budget_id' => $budget->id,
                'item_name' => $item['item_name'],
                'amount' => $item['amount'] ?? 0,
                'responsible_person' => $item['responsible_person'] ?? '—',
                'notes' => $item['notes'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }
}
