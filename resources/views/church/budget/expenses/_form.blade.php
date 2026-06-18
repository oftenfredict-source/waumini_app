@php
    $expense = $expense ?? null;
    $selectedBudgetId = old('budget_id', $expense?->budget_id ?? request('budget_id'));
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Budget *</label>
            <select name="budget_id" id="budget_id" class="form-control @error('budget_id') is-invalid @enderror" required>
                @foreach($budgets as $budget)
                    @php
                        $remaining = max(0, (float) $budget->total_budget - (float) $budget->spent_amount);
                    @endphp
                    <option value="{{ $budget->id }}"
                        data-name="{{ $budget->budget_name }}"
                        data-total="{{ (float) $budget->total_budget }}"
                        data-allocated="{{ (float) $budget->allocated_amount }}"
                        data-spent="{{ (float) $budget->spent_amount }}"
                        data-remaining="{{ $remaining }}"
                        data-start="{{ $budget->start_date?->format('M d, Y') }}"
                        data-end="{{ $budget->end_date?->format('M d, Y') }}"
                        data-primary="{{ $budget->primary_offering_type ?? '—' }}"
                        @selected($selectedBudgetId == $budget->id)>
                        {{ $budget->budget_name }} ({{ $budget->fiscal_year }})
                    </option>
                @endforeach
            </select>
            @error('budget_id')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-12">
        <div class="alert alert-info" id="budgetOverview" style="display: none;">
            <div class="d-flex justify-content-between flex-wrap">
                <div>
                    <strong id="budgetOverviewName">—</strong>
                    <div class="small text-muted">
                        Period: <span id="budgetOverviewPeriod">—</span> |
                        Primary Offering: <span id="budgetOverviewPrimary">—</span>
                    </div>
                </div>
                <div class="text-md-right mt-2 mt-md-0">
                    <div><strong>Total:</strong> TZS <span id="budgetOverviewTotal">0.00</span></div>
                    <div><strong>Allocated:</strong> TZS <span id="budgetOverviewAllocated">0.00</span></div>
                    <div><strong>Spent:</strong> TZS <span id="budgetOverviewSpent">0.00</span></div>
                    <div><strong>Remaining:</strong> TZS <span id="budgetOverviewRemaining">0.00</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Category *</label>
            <select name="expense_category" class="form-control @error('expense_category') is-invalid @enderror" required>
                @foreach($expenseCategories as $cat)
                    <option value="{{ $cat->value }}" @selected(old('expense_category', $expense?->expense_category?->value) === $cat->value)>
                        {{ $cat->label() }}
                    </option>
                @endforeach
            </select>
            @error('expense_category')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-8">
        <div class="form-group">
            <label>Expense Name *</label>
            <input type="text" name="expense_name" id="expense_name" class="form-control @error('expense_name') is-invalid @enderror"
                value="{{ old('expense_name', $expense?->expense_name) }}" readonly required>
            <small class="text-muted">Automatically filled from the selected budget.</small>
            @error('expense_name')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Amount (TZS) *</label>
            <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror"
                value="{{ old('amount', $expense?->amount) }}" required>
            <small class="text-muted" id="amountLimitHint"></small>
            @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Expense Date *</label>
            <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror"
                value="{{ old('expense_date', $expense?->expense_date?->toDateString() ?? now()->toDateString()) }}" required>
            @error('expense_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Payment Method *</label>
            <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                @foreach($paymentMethods as $method)
                    <option value="{{ $method->value }}" @selected(old('payment_method', $expense?->payment_method?->value) === $method->value)>
                        {{ $method->label() }}
                    </option>
                @endforeach
            </select>
            @error('payment_method')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-4" id="referenceGroup">
        <div class="form-group">
            <label>Reference Number</label>
            <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror"
                value="{{ old('reference_number', $expense?->reference_number) }}">
            @error('reference_number')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Vendor</label>
            <input type="text" name="vendor" class="form-control @error('vendor') is-invalid @enderror"
                value="{{ old('vendor', $expense?->vendor) }}">
            @error('vendor')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Receipt Number</label>
            <input type="text" name="receipt_number" class="form-control @error('receipt_number') is-invalid @enderror"
                value="{{ old('receipt_number', $expense?->receipt_number) }}">
            @error('receipt_number')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $expense?->description) }}</textarea>
            @error('description')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $expense?->notes) }}</textarea>
            @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const paymentSelect = document.getElementById('payment_method');
    const referenceGroup = document.getElementById('referenceGroup');
    const budgetSelect = document.getElementById('budget_id');
    const expenseName = document.getElementById('expense_name');
    const amountInput = document.getElementById('amount');
    const amountLimitHint = document.getElementById('amountLimitHint');
    const budgetOverview = document.getElementById('budgetOverview');
    const isEditing = @json((bool) $expense);

    function toggleReference() {
        if (!paymentSelect || !referenceGroup) return;
        const needsRef = ['bank_transfer', 'mobile_money', 'cheque'].includes(paymentSelect.value);
        referenceGroup.style.display = needsRef ? '' : 'none';
    }

    function formatMoney(value) {
        return Number(value || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function updateBudgetOverview(forceExpenseName = false) {
        if (!budgetSelect || !budgetSelect.selectedOptions.length) return;

        const option = budgetSelect.selectedOptions[0];
        const remaining = Number(option.dataset.remaining || 0);

        if (budgetOverview) {
            budgetOverview.style.display = '';
            document.getElementById('budgetOverviewName').textContent = option.dataset.name || '—';
            document.getElementById('budgetOverviewPeriod').textContent = `${option.dataset.start || '—'} - ${option.dataset.end || '—'}`;
            document.getElementById('budgetOverviewPrimary').textContent = option.dataset.primary || '—';
            document.getElementById('budgetOverviewTotal').textContent = formatMoney(option.dataset.total);
            document.getElementById('budgetOverviewAllocated').textContent = formatMoney(option.dataset.allocated);
            document.getElementById('budgetOverviewSpent').textContent = formatMoney(option.dataset.spent);
            document.getElementById('budgetOverviewRemaining').textContent = formatMoney(remaining);
        }

        if (amountInput) {
            amountInput.max = remaining.toFixed(2);
        }

        if (amountLimitHint) {
            amountLimitHint.textContent = `Maximum allowed for this budget: TZS ${formatMoney(remaining)}`;
        }

        if (expenseName && (forceExpenseName || (!isEditing && !expenseName.value))) {
            expenseName.value = option.dataset.name || '';
        }
    }

    paymentSelect?.addEventListener('change', toggleReference);
    budgetSelect?.addEventListener('change', () => updateBudgetOverview(true));

    toggleReference();
    updateBudgetOverview(false);
});
</script>
@endpush

