@extends('layouts.church')

@section('title', __('pages.expenses.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-receipt',
    'title' => __('pages.expenses.title'),
    'subtitle' => __('pages.expenses.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.budget_expenses'), 'route' => 'church.budget.index'],
        ['label' => __('pages.budget.expenses')],
    ],
])

<div class="row mb-3">
    <div class="col-md-9">
        <form method="GET" class="form-inline">
            <select name="budget_id" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_budgets') }}</option>
                @foreach($budgets as $b)
                    <option value="{{ $b->id }}" @selected(($filters['budget_id'] ?? '') == $b->id)>
                        {{ $b->budget_name }}
                    </option>
                @endforeach
            </select>
            <select name="expense_category" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_categories') }}</option>
                @foreach($expenseCategories as $cat)
                    <option value="{{ $cat->value }}" @selected(($filters['expense_category'] ?? '') === $cat->value)>{{ $cat->label() }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_statuses') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <input type="date" name="from" class="form-control mr-2 mb-2" value="{{ $filters['from'] ?? '' }}" title="{{ __('common.from') }}">
            <input type="date" name="to" class="form-control mr-2 mb-2" value="{{ $filters['to'] ?? '' }}" title="{{ __('common.to') }}">
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> {{ __('common.filter') }}</button>
        </form>
    </div>
    <div class="col-md-3 text-md-right">
        @can('create', \App\Models\Expense::class)
            <a href="{{ route('church.expenses.create', ['budget_id' => request('budget_id')]) }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> {{ __('pages.expenses.add_expense') }}
            </a>
        @endcan
    </div>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm">
            <thead>
                <tr>
                    <th>{{ __('pages.shared.budget') }}</th>
                    <th>{{ __('common.category') }}</th>
                    <th>{{ __('common.name') }}</th>
                    <th>{{ __('common.amount') }}</th>
                    <th>{{ __('common.date') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th>{{ __('common.approval') }}</th>
                    <th width="140">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                    <tr>
                        <td><strong>{{ $expense->budget?->budget_name ?? '—' }}</strong></td>
                        <td><span class="badge badge-secondary">{{ $expense->expense_category->label() }}</span></td>
                        <td>{{ $expense->expense_name }}</td>
                        <td><strong class="{{ $expense->status === \App\Enums\ExpenseStatus::Paid ? 'text-success' : 'text-danger' }}">
                            TZS {{ number_format($expense->amount, 2) }}</strong></td>
                        <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                        <td><span class="badge badge-{{ $expense->status->badgeClass() }}">{{ $expense->status->label() }}</span></td>
                        <td><span class="badge badge-{{ $expense->approval_status->badgeClass() }}">{{ $expense->approval_status->label() }}</span></td>
                        <td>
                            <a href="{{ route('church.expenses.show', $expense) }}" class="btn btn-sm btn-info" title="{{ __('common.view') }}">
                                <i class="fa fa-eye"></i>
                            </a>
                            @can('update', $expense)
                                <a href="{{ route('church.expenses.edit', $expense) }}" class="btn btn-sm btn-primary ml-1" title="{{ __('common.edit') }}">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            @endcan
                            @can('delete', $expense)
                                <form method="POST" action="{{ route('church.expenses.destroy', $expense) }}" class="d-inline"
                                    data-swal-confirm="Delete this expense?"
                                    data-swal-delete
                                    data-swal-confirm-text="{{ __('common.yes_delete') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger ml-1" title="{{ __('common.delete') }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">{{ __('pages.expenses.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $expenses->links() }}
</div>
@endsection
