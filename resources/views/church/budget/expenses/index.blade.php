@extends('layouts.church')

@section('title', 'Expenses')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-receipt"></i> Expenses</h1>
        <p>Record and approve budget expenses.</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.budget.index') }}">Budget & Expenses</a></li>
        <li class="breadcrumb-item">Expenses</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-9">
        <form method="GET" class="form-inline">
            <select name="budget_id" class="form-control mr-2 mb-2">
                <option value="">All budgets</option>
                @foreach($budgets as $b)
                    <option value="{{ $b->id }}" @selected(($filters['budget_id'] ?? '') == $b->id)>
                        {{ $b->budget_name }}
                    </option>
                @endforeach
            </select>
            <select name="expense_category" class="form-control mr-2 mb-2">
                <option value="">All categories</option>
                @foreach($expenseCategories as $cat)
                    <option value="{{ $cat->value }}" @selected(($filters['expense_category'] ?? '') === $cat->value)>{{ $cat->label() }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <input type="date" name="from" class="form-control mr-2 mb-2" value="{{ $filters['from'] ?? '' }}" title="From">
            <input type="date" name="to" class="form-control mr-2 mb-2" value="{{ $filters['to'] ?? '' }}" title="To">
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> Filter</button>
        </form>
    </div>
    <div class="col-md-3 text-md-right">
        @can('create', \App\Models\Expense::class)
            <a href="{{ route('church.expenses.create', ['budget_id' => request('budget_id')]) }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> Add Expense
            </a>
        @endcan
    </div>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm">
            <thead>
                <tr>
                    <th>Budget</th>
                    <th>Category</th>
                    <th>Name</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Approval</th>
                    <th width="140">Actions</th>
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
                            <a href="{{ route('church.expenses.show', $expense) }}" class="btn btn-sm btn-info" title="View">
                                <i class="fa fa-eye"></i>
                            </a>
                            @can('update', $expense)
                                <a href="{{ route('church.expenses.edit', $expense) }}" class="btn btn-sm btn-primary ml-1" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            @endcan
                            @can('delete', $expense)
                                <form method="POST" action="{{ route('church.expenses.destroy', $expense) }}" class="d-inline"
                                    data-swal-confirm="Delete this expense?"
                                    data-swal-delete
                                    data-swal-confirm-text="Yes, delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger ml-1" title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No expenses found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $expenses->links() }}
</div>
@endsection

