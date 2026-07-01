@php
    $budget = $budget ?? null;
    $lineItems = $budget?->lineItems?->values()->toArray() ?? [];
    $existingAllocations = isset($existingAllocations) ? $existingAllocations : [];
    $availableAmounts = isset($availableAmounts) ? $availableAmounts : [];
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.budget.form_budget_name') }} *</label>
            <input type="text" name="budget_name" class="form-control @error('budget_name') is-invalid @enderror"
                value="{{ old('budget_name', $budget?->budget_name) }}" required>
            @error('budget_name')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('pages.budget.form_budget_type') }} *</label>
            <select name="budget_type" class="form-control @error('budget_type') is-invalid @enderror" required>
                @foreach($budgetTypes as $type)
                    <option value="{{ $type->value }}" @selected(old('budget_type', $budget?->budget_type?->value) === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>
            @error('budget_type')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('common.status') }}</label>
            <select name="status" class="form-control @error('status') is-invalid @enderror">
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $budget?->status?->value ?? 'active') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            @error('status')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.shared.purpose') }}</label>
            <input type="text" name="purpose" class="form-control @error('purpose') is-invalid @enderror"
                value="{{ old('purpose', $budget?->purpose) }}">
            @error('purpose')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.budget.form_primary_offering') }}</label>
            <select name="primary_offering_type" class="form-control @error('primary_offering_type') is-invalid @enderror">
                <option value="">—</option>
                @foreach($offeringTypes as $type)
                    <option value="{{ $type->value }}" @selected(old('primary_offering_type', $budget?->primary_offering_type) === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>
            @error('primary_offering_type')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('pages.budget.form_fiscal_year') }} *</label>
            <input type="number" name="fiscal_year" class="form-control @error('fiscal_year') is-invalid @enderror"
                value="{{ old('fiscal_year', $budget?->fiscal_year) }}" required>
            @error('fiscal_year')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('pages.shared.start_date') }} *</label>
            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                value="{{ old('start_date', $budget?->start_date?->toDateString()) }}" required>
            @error('start_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('pages.shared.end_date') }} *</label>
            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                value="{{ old('end_date', $budget?->end_date?->toDateString()) }}" required>
            @error('end_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('pages.budget.form_total_budget') }} *</label>
            <input type="number" step="0.01" min="0" name="total_budget" class="form-control @error('total_budget') is-invalid @enderror"
                value="{{ old('total_budget', $budget?->total_budget) }}" required>
            @error('total_budget')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>{{ __('common.description') }}</label>
            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $budget?->description) }}</textarea>
            @error('description')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
</div>

<hr>

<h4 class="tile-title">{{ __('pages.budget.line_items') }}</h4>
<div id="lineItemsContainer">
    @php
        $initialCount = max(1, count($lineItems));
    @endphp
    @for($i = 0; $i < $initialCount; $i++)
        @php
            $item = $lineItems[$i] ?? [];
        @endphp
        <div class="row mb-2 line-item-row" data-index="{{ $i }}">
            <div class="col-md-4">
                <div class="form-group">
                    <label>{{ __('pages.budget.form_item_name') }}</label>
                    <input type="text" name="line_items[{{ $i }}][item_name]"
                        class="form-control form-control-sm @error('line_items.'.$i.'.item_name') is-invalid @enderror"
                        value="{{ old('line_items.'.$i.'.item_name', $item['item_name'] ?? '') }}">
                    @error('line_items.'.$i.'.item_name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>{{ __('common.amount') }}</label>
                    <input type="number" step="0.01" min="0" name="line_items[{{ $i }}][amount]"
                        class="form-control form-control-sm @error('line_items.'.$i.'.amount') is-invalid @enderror"
                        value="{{ old('line_items.'.$i.'.amount', $item['amount'] ?? 0) }}">
                    @error('line_items.'.$i.'.amount')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>{{ __('pages.budget.form_responsible_person') }}</label>
                    <input type="text" name="line_items[{{ $i }}][responsible_person]"
                        class="form-control form-control-sm @error('line_items.'.$i.'.responsible_person') is-invalid @enderror"
                        value="{{ old('line_items.'.$i.'.responsible_person', $item['responsible_person'] ?? '') }}">
                    @error('line_items.'.$i.'.responsible_person')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm remove-line-item" style="margin-top: 28px;" @disabled($i===0)>
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{ __('pages.shared.notes') }}</label>
                    <textarea name="line_items[{{ $i }}][notes]" class="form-control form-control-sm @error('line_items.'.$i.'.notes') is-invalid @enderror" rows="2">{{ old('line_items.'.$i.'.notes', $item['notes'] ?? '') }}</textarea>
                    @error('line_items.'.$i.'.notes')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>
    @endfor
</div>

<button type="button" class="btn btn-outline-primary btn-sm" id="addLineItemBtn">
    <i class="fa fa-plus"></i> {{ __('pages.budget.add_line_item') }}
</button>

<hr>

<h4 class="tile-title">{{ __('pages.budget.funding_allocations') }}</h4>
<p class="text-muted small mb-3">
    {{ __('pages.budget.funding_allocations_hint') }}
    <strong>{{ __('pages.budget.total_available', ['amount' => number_format(array_sum($availableAmounts), 2)]) }}</strong>
</p>
<div class="row">
    @foreach($offeringTypes as $type)
        @php
            $key = $type->value;
            $value = old('funding_allocations.'.$key, $existingAllocations[$key] ?? 0);
            $available = (float) ($availableAmounts[$key] ?? 0);
        @endphp
        <div class="col-md-4 mb-2">
            <div class="form-group">
                <label>{{ $type->label() }}</label>
                <input type="number" step="0.01" min="0" max="{{ $available }}" placeholder="{{ number_format($available, 2, '.', '') }}" name="funding_allocations[{{ $key }}]" class="form-control @error('funding_allocations.'.$key) is-invalid @enderror"
                    value="{{ $value }}">
                <small class="text-muted d-block">
                    {{ __('pages.budget.available', ['amount' => number_format($available, 2)]) }}
                </small>
                @error('funding_allocations.'.$key)<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('lineItemsContainer');
    const addBtn = document.getElementById('addLineItemBtn');
    if (!container || !addBtn) return;

    function reindex() {
        const rows = container.querySelectorAll('.line-item-row');
        rows.forEach((row, idx) => {
            row.dataset.index = idx;
        });
    }

    addBtn.addEventListener('click', function () {
        const rows = container.querySelectorAll('.line-item-row');
        const nextIndex = rows.length;

        const firstRow = rows[0];
        const clone = firstRow.cloneNode(true);
        clone.dataset.index = nextIndex;
        clone.querySelectorAll('input,textarea').forEach(el => {
            const name = el.getAttribute('name');
            if (!name) return;
            el.setAttribute('name', name.replace(/line_items\[\d+\]/, `line_items[${nextIndex}]`));
            if (el.tagName.toLowerCase() === 'textarea') {
                el.value = '';
            } else {
                el.value = '';
            }
        });
        const removeBtn = clone.querySelector('.remove-line-item');
        if (removeBtn) {
            removeBtn.disabled = false;
        }
        container.appendChild(clone);
    });

    container.addEventListener('click', function (e) {
        if (!e.target.classList.contains('remove-line-item')) return;
        const row = e.target.closest('.line-item-row');
        if (!row) return;
        row.remove();
        reindex();
    });
});
</script>
@endpush
