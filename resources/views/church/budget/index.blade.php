@extends('layouts.church')

@section('title', __('pages.budget.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-briefcase',
    'title' => __('pages.budget.title'),
    'subtitle' => __('pages.budget.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.budget_expenses')],
    ],
])

<div class="row mb-3">
    <div class="col-md-9">
        <form method="GET" class="form-inline">
            <select name="fiscal_year" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_years') }}</option>
                @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                    <option value="{{ $y }}" @selected(($filters['fiscal_year'] ?? '') == $y)>{{ $y }}</option>
                @endfor
            </select>
            <select name="budget_type" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_types') }}</option>
                @foreach($budgetTypes as $type)
                    <option value="{{ $type->value }}" @selected(($filters['budget_type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_statuses') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> {{ __('common.filter') }}</button>
        </form>
    </div>
    <div class="col-md-3 text-md-right">
        @can('create', \App\Models\Budget::class)
            <a href="{{ route('church.budget.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> {{ __('pages.budget.add_budget') }}
            </a>
        @endcan
        @can('viewAny', \App\Models\Expense::class)
            <a href="{{ route('church.expenses.index') }}" class="btn btn-outline-primary mb-2 ml-2">
                <i class="fa fa-receipt"></i> {{ __('pages.budget.expenses') }}
            </a>
        @endcan
    </div>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>{{ __('pages.shared.budget') }}</th>
                    <th>{{ __('common.type') }}</th>
                    <th>{{ __('common.total') }}</th>
                    <th>{{ __('pages.shared.spent') }}</th>
                    <th>{{ __('pages.shared.remaining') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th>{{ __('common.approval') }}</th>
                    <th width="140">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($budgets as $budget)
                    <tr>
                        <td>
                            <strong>{{ $budget->budget_name }}</strong>
                            <div class="small text-muted">{{ $budget->start_date?->format('M d, Y') }} - {{ $budget->end_date?->format('M d, Y') }}</div>
                        </td>
                        <td><span class="badge badge-info">{{ $budget->budget_type->label() }}</span></td>
                        <td><strong>TZS {{ number_format($budget->total_budget, 2) }}</strong></td>
                        <td>TZS {{ number_format($budget->spent_amount, 2) }}</td>
                        <td>TZS {{ number_format($budget->remainingAmount(), 2) }}</td>
                        <td><span class="badge badge-{{ $budget->status->badgeClass() }}">{{ $budget->status->label() }}</span></td>
                        <td>
                            <span class="badge badge-{{ $budget->approval_status->badgeClass() }}">
                                {{ $budget->approval_status->label() }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('church.budget.show', $budget) }}" class="btn btn-info" title="{{ __('common.view') }}">
                                    <i class="fa fa-eye"></i>
                                </a>
                                @can('update', $budget)
                                    <a href="{{ route('church.budget.edit', $budget) }}" class="btn btn-primary" title="{{ __('common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            {{ __('pages.budget.empty') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $budgets->links() }}
</div>
@endsection
