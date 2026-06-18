<?php

namespace App\Http\Controllers\Church;

use App\Enums\ExpenseCategory;
use App\Enums\ExpenseStatus;
use App\Enums\FinancePaymentMethod;
use App\Enums\FinancialApprovalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\MarkExpensePaidRequest;
use App\Http\Requests\Church\StoreExpenseRequest;
use App\Http\Requests\Church\UpdateExpenseRequest;
use App\Models\Budget;
use App\Models\Expense;
use App\Services\Church\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseService $expenseService,
    ) {
        $this->authorizeResource(Expense::class, 'expense');
    }

    public function index(Request $request): View
    {
        $church = $request->user()->church;

        $query = Expense::forChurch($church->id)
            ->with(['budget', 'recorder', 'approver'])
            ->latest('expense_date');

        if ($budgetId = $request->integer('budget_id')) {
            $query->where('budget_id', $budgetId);
        }

        if ($category = $request->string('expense_category')->trim()->toString()) {
            $query->where('expense_category', $category);
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        if ($from = $request->string('from')->trim()->toString()) {
            $query->whereDate('expense_date', '>=', $from);
        }

        if ($to = $request->string('to')->trim()->toString()) {
            $query->whereDate('expense_date', '<=', $to);
        }

        $expenses = $query->paginate(20)->withQueryString();

        return view('church.budget.expenses.index', [
            'expenses' => $expenses,
            'budgets' => $this->approvedBudgets($church->id),
            'expenseCategories' => ExpenseCategory::cases(),
            'statuses' => ExpenseStatus::cases(),
            'filters' => $request->only(['budget_id', 'expense_category', 'status', 'from', 'to']),
        ]);
    }

    public function create(): View
    {
        $church = auth()->user()->church;

        return view('church.budget.expenses.create', [
            'expense' => null,
            'budgets' => $this->approvedBudgets($church->id),
            'expenseCategories' => ExpenseCategory::cases(),
            'paymentMethods' => FinancePaymentMethod::cases(),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $church = $request->user()->church;

        $expense = $this->expenseService->create(
            $church,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('church.expenses.show', $expense)
            ->with('success', 'Expense submitted for approval.');
    }

    public function show(Expense $expense): View
    {
        $expense->load(['budget', 'recorder', 'approver']);

        return view('church.budget.expenses.show', [
            'expense' => $expense,
            'paymentMethods' => FinancePaymentMethod::cases(),
        ]);
    }

    public function edit(Expense $expense): View
    {
        $expense->load(['budget']);

        return view('church.budget.expenses.edit', [
            'expense' => $expense,
            'budgets' => $this->approvedBudgets($expense->church_id),
            'expenseCategories' => ExpenseCategory::cases(),
            'paymentMethods' => FinancePaymentMethod::cases(),
        ]);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->expenseService->update($expense, $request->validated());

        return redirect()
            ->route('church.expenses.show', $expense)
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->expenseService->delete($expense);

        return redirect()
            ->route('church.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    public function markPaid(MarkExpensePaidRequest $request, Expense $expense): RedirectResponse
    {
        try {
            $this->expenseService->markPaid($expense, $request->user());
        } catch (\Throwable $e) {
            return redirect()
                ->route('church.expenses.show', $expense)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('church.expenses.show', $expense)
            ->with('success', 'Expense marked as paid.');
    }

    private function approvedBudgets(int $churchId): \Illuminate\Support\Collection
    {
        return Budget::forChurch($churchId)
            ->where('approval_status', FinancialApprovalStatus::Approved)
            ->orderByDesc('fiscal_year')
            ->orderByDesc('start_date')
            ->get([
                'id',
                'budget_name',
                'fiscal_year',
                'start_date',
                'end_date',
                'total_budget',
                'allocated_amount',
                'spent_amount',
                'primary_offering_type',
            ]);
    }
}

