<?php

namespace App\Http\Controllers\Church;

use App\Enums\BudgetStatus;
use App\Enums\BudgetType;
use App\Enums\OfferingType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\AllocateBudgetFundsRequest;
use App\Http\Requests\Church\StoreBudgetRequest;
use App\Http\Requests\Church\UpdateBudgetRequest;
use App\Models\Budget;
use App\Services\Church\BudgetFundingService;
use App\Services\Church\BudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function __construct(
        private readonly BudgetService $budgetService,
        private readonly BudgetFundingService $budgetFundingService,
    ) {
        $this->authorizeResource(Budget::class, 'budget');
    }

    public function index(Request $request): View
    {
        $church = $request->user()->church;

        $query = Budget::forChurch($church->id)
            ->withCount('expenses')
            ->latest('created_at');

        if ($year = $request->integer('fiscal_year')) {
            $query->where('fiscal_year', $year);
        }

        if ($type = $request->string('budget_type')->trim()->toString()) {
            $query->where('budget_type', $type);
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        $budgets = $query->paginate(20)->withQueryString();

        return view('church.budget.index', [
            'budgets' => $budgets,
            'budgetTypes' => BudgetType::cases(),
            'statuses' => BudgetStatus::cases(),
            'filters' => $request->only(['fiscal_year', 'budget_type', 'status']),
        ]);
    }

    public function create(): View
    {
        $church = auth()->user()->church;

        return view('church.budget.create', [
            'budget' => null,
            'budgets' => null,
            'budgetTypes' => BudgetType::cases(),
            'offeringTypes' => OfferingType::cases(),
            'statuses' => BudgetStatus::cases(),
            'availableAmounts' => $this->budgetFundingService->getAvailableAmountsAfterAllocations($church->id),
            'church' => $church,
        ]);
    }

    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $church = $request->user()->church;

        $budget = $this->budgetService->create(
            $church,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('church.budget.show', $budget)
            ->with('success', 'Budget created successfully. Pending approval.');
    }

    public function show(Budget $budget): View
    {
        $budget->load(['lineItems', 'offeringAllocations', 'expenses' => fn ($q) => $q->latest('expense_date')]);

        $offeringTypes = OfferingType::cases();

        $existingAllocations = $budget->offeringAllocations
            ->keyBy('offering_type')
            ->map(fn ($a) => (float) $a->allocated_amount)
            ->toArray();

        return view('church.budget.show', [
            'budget' => $budget,
            'offeringTypes' => $offeringTypes,
            'existingAllocations' => $existingAllocations,
            'availableAmounts' => $this->budgetFundingService->getAvailableAmountsAfterAllocations($budget->church_id),
        ]);
    }

    public function edit(Budget $budget): View
    {
        $budget->load(['lineItems', 'offeringAllocations']);

        $existingAllocations = $budget->offeringAllocations
            ->keyBy('offering_type')
            ->map(fn ($a) => (float) $a->allocated_amount)
            ->toArray();

        return view('church.budget.edit', [
            'budget' => $budget,
            'budgetTypes' => BudgetType::cases(),
            'offeringTypes' => OfferingType::cases(),
            'statuses' => BudgetStatus::cases(),
            'existingAllocations' => $existingAllocations,
            'availableAmounts' => $this->budgetFundingService->getAvailableAmountsAfterAllocations($budget->church_id),
        ]);
    }

    public function update(UpdateBudgetRequest $request, Budget $budget): RedirectResponse
    {
        $this->budgetService->update($budget, $request->validated());

        return redirect()
            ->route('church.budget.show', $budget)
            ->with('success', 'Budget updated successfully.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->budgetService->delete($budget);

        return redirect()
            ->route('church.budget.index')
            ->with('success', 'Budget deleted successfully.');
    }

    public function allocateFunds(AllocateBudgetFundsRequest $request, Budget $budget): RedirectResponse
    {
        $allocations = $request->validated('allocations');

        $this->budgetService->allocateFunds($budget, $allocations);

        return redirect()
            ->route('church.budget.show', $budget)
            ->with('success', 'Funds allocated successfully.');
    }
}

