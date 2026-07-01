@extends('layouts.church')

@section('title', __('pages.budget.show_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-briefcase',
    'title' => __('pages.budget.show_title'),
    'subtitle' => $budget->budget_name,
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.budget_expenses'), 'route' => 'church.budget.index'],
        ['label' => __('pages.shared.breadcrumb_details')],
    ],
])

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.shared.information', ['module' => __('pages.budget.item')]) }}</h3>
            <table class="table table-borderless table-sm">
                <tr>
                    <th width="170">{{ __('common.status') }}</th>
                    <td>
                        <span class="badge badge-{{ $budget->status->badgeClass() }}">
                            {{ $budget->status->label() }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>{{ __('common.approval') }}</th>
                    <td>
                        <span class="badge badge-{{ $budget->approval_status->badgeClass() }}">
                            {{ $budget->approval_status->label() }}
                        </span>
                    </td>
                </tr>
                <tr><th>{{ __('common.type') }}</th><td>{{ $budget->budget_type->label() }}</td></tr>
                <tr><th>{{ __('pages.shared.purpose') }}</th><td>{{ $budget->purpose ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.budget.show_primary_offering') }}</th><td>{{ $budget->primary_offering_type ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.budget.show_fiscal_year') }}</th><td>{{ $budget->fiscal_year }}</td></tr>
                <tr><th>{{ __('pages.shared.period') }}</th><td>{{ $budget->start_date->format('M d, Y') }} - {{ $budget->end_date->format('M d, Y') }}</td></tr>
                <tr><th>{{ __('pages.budget.show_total_budget') }}</th><td><strong>TZS {{ number_format($budget->total_budget, 2) }}</strong></td></tr>
                <tr><th>{{ __('pages.budget.show_allocated') }}</th><td>TZS {{ number_format($budget->offeringAllocations()->sum('allocated_amount'), 2) }}</td></tr>
                <tr><th>{{ __('pages.budget.show_spent_paid') }}</th><td>TZS {{ number_format($budget->spent_amount, 2) }}</td></tr>
                <tr>
                    <th>{{ __('pages.shared.remaining') }}</th>
                    <td>TZS {{ number_format($budget->remainingAmount(), 2) }}</td>
                </tr>
                <tr><th>{{ __('pages.budget.show_utilization') }}</th><td>{{ $budget->utilizationPercentage() }}%</td></tr>
            </table>
        </div>

        <div class="tile mt-3">
            <h3 class="tile-title">{{ __('pages.budget.funding_allocations_title') }}</h3>
            @forelse($budget->offeringAllocations as $allocation)
                <div class="mb-2 pb-2 border-bottom d-flex justify-content-between">
                    <div>
                        <strong>{{ $allocation->offering_type }}</strong>
                        @if($allocation->is_primary)
                            <span class="badge badge-primary ml-2">{{ __('pages.budget.primary_badge') }}</span>
                        @endif
                    </div>
                    <div>
                        <span>TZS {{ number_format($allocation->allocated_amount, 2) }}</span>
                        <span class="text-muted"> {{ __('pages.budget.used_label') }} TZS {{ number_format($allocation->used_amount, 2) }}</span>
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0">{{ __('pages.budget.no_allocations') }}</p>
            @endforelse

            @can('allocateFunds', $budget)
                <hr>
                <h4 class="tile-title">{{ __('pages.budget.allocate_additional') }}</h4>
                <form method="POST" action="{{ route('church.budget.allocate-funds', $budget) }}">
                    @csrf
                    <div class="row">
                        @foreach($offeringTypes as $type)
                            <div class="col-md-4 mb-2">
                                <div class="form-group">
                                    <label>{{ $type->label() }}</label>
                                    <input type="number" step="0.01" min="0"
                                        name="allocations[{{ $type->value }}]"
                                        class="form-control"
                                        value="0">
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> {{ __('pages.budget.allocate') }}</button>
                </form>
            @endcan
        </div>

        <div class="tile mt-3">
            <h3 class="tile-title">{{ __('pages.budget.line_items') }}</h3>
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>{{ __('pages.budget.form_item_name') }}</th>
                            <th>{{ __('common.amount') }}</th>
                            <th>{{ __('pages.budget.form_responsible_person') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($budget->lineItems as $item)
                            <tr>
                                <td>{{ $item->item_name }}</td>
                                <td><strong>TZS {{ number_format($item->amount, 2) }}</strong></td>
                                <td>{{ $item->responsible_person }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">{{ __('pages.budget.no_line_items') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ route('church.budget.edit', $budget) }}" class="btn btn-outline-primary btn-sm mt-2">{{ __('pages.budget.edit_line_items') }}</a>
        </div>

        <div class="tile mt-3">
            <h3 class="tile-title">{{ __('pages.budget.expenses_this_budget') }}</h3>
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>{{ __('common.category') }}</th>
                            <th>{{ __('common.name') }}</th>
                            <th>{{ __('common.amount') }}</th>
                            <th>{{ __('common.date') }}</th>
                            <th>{{ __('common.status') }}</th>
                            <th width="140">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($budget->expenses as $expense)
                            <tr>
                                <td><span class="badge badge-secondary">{{ $expense->expense_category->label() }}</span></td>
                                <td>{{ $expense->expense_name }}</td>
                                <td><strong class="text-danger">TZS {{ number_format($expense->amount, 2) }}</strong></td>
                                <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge badge-{{ $expense->status->badgeClass() }}">{{ $expense->status->label() }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('church.expenses.show', $expense) }}" class="btn btn-sm btn-info">{{ __('common.view') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">{{ __('pages.budget.no_expenses') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @can('create', \App\Models\Expense::class)
                @if($budget->approval_status === \App\Enums\FinancialApprovalStatus::Approved)
                    <a href="{{ route('church.expenses.create', ['budget_id' => $budget->id]) }}" class="btn btn-primary btn-sm mt-2">
                        <i class="fa fa-plus"></i> {{ __('pages.budget.record_expense') }}
                    </a>
                @endif
            @endcan
        </div>
    </div>

    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('common.actions') }}</h3>
            <a href="{{ route('church.budget.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> {{ __('pages.shared.back_to', ['module' => __('menu.budget_expenses')]) }}
            </a>
            @can('update', $budget)
                <a href="{{ route('church.budget.edit', $budget) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> {{ __('pages.shared.edit_item', ['item' => __('pages.budget.item')]) }}
                </a>
            @endcan
            @can('delete', $budget)
                <form method="POST" action="{{ route('church.budget.destroy', $budget) }}" class="mt-2"
                    data-swal-confirm="{{ __('pages.shared.delete_confirm', ['item' => __('pages.budget.item')]) }}"
                    data-swal-delete
                    data-swal-confirm-text="{{ __('common.yes_delete') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> {{ __('pages.shared.delete_item', ['item' => __('pages.budget.item')]) }}
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
