@extends('layouts.church')

@section('title', 'Expense Details')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-receipt"></i> Expense Details</h1>
        <p>{{ $expense->expense_name }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.budget.index') }}">Budget & Expenses</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.expenses.index') }}">Expenses</a></li>
        <li class="breadcrumb-item">Details</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">Expense Information</h3>
            <table class="table table-borderless table-sm">
                <tr>
                    <th width="180">Status</th>
                    <td>
                        <span class="badge badge-{{ $expense->status->badgeClass() }}">
                            {{ $expense->status->label() }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Approval Status</th>
                    <td>
                        <span class="badge badge-{{ $expense->approval_status->badgeClass() }}">
                            {{ $expense->approval_status->label() }}
                        </span>
                    </td>
                </tr>
                <tr><th>Budget</th><td>{{ $expense->budget?->budget_name ?? '—' }}</td></tr>
                <tr><th>Category</th><td><span class="badge badge-secondary">{{ $expense->expense_category->label() }}</span></td></tr>
                <tr><th>Amount</th><td><strong>TZS {{ number_format($expense->amount, 2) }}</strong></td></tr>
                <tr><th>Date</th><td>{{ $expense->expense_date->format('M d, Y') }}</td></tr>
                <tr><th>Payment Method</th><td>{{ $expense->payment_method?->label() ?? '—' }}</td></tr>
                <tr><th>Reference</th><td>{{ $expense->reference_number ?? '—' }}</td></tr>
                <tr><th>Vendor</th><td>{{ $expense->vendor ?? '—' }}</td></tr>
                <tr><th>Receipt</th><td>{{ $expense->receipt_number ?? '—' }}</td></tr>
                <tr><th>Notes</th><td>{{ $expense->notes ?? '—' }}</td></tr>
                <tr><th>Recorded By</th><td>{{ $expense->recorder?->name ?? '—' }}</td></tr>
                <tr><th>Recorded On</th><td>{{ $expense->created_at->format('M d, Y H:i') }}</td></tr>
                @if($expense->approval_notes)
                    <tr><th>Approval Notes</th><td>{{ $expense->approval_notes }}</td></tr>
                @endif
                @if($expense->rejection_reason)
                    <tr><th>Rejection Reason</th><td class="text-danger">{{ $expense->rejection_reason }}</td></tr>
                @endif
                @if($expense->approved_at)
                    <tr><th>Approved At</th><td>{{ $expense->approved_at?->format('M d, Y H:i') }}</td></tr>
                @endif
                @if($expense->paid_at)
                    <tr><th>Paid At</th><td>{{ $expense->paid_at?->format('M d, Y H:i') }}</td></tr>
                @endif
            </table>
        </div>

        @can('markPaid', $expense)
            <div class="tile mt-3">
                <h3 class="tile-title">Mark as Paid</h3>
                @if($expense->canBeMarkedPaid())
                    <p class="text-muted">This will update the related budget totals and deduct from allocations.</p>
                <form method="POST" action="{{ route('church.budget.expenses.mark-paid', $expense) }}"
                        data-swal-confirm="Mark this expense as paid? This action cannot be undone.">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Mark Paid</button>
                    </form>
                @else
                    <div class="alert alert-info mb-0">
                        This expense cannot be marked as paid right now.
                    </div>
                @endif
            </div>
        @endcan
    </div>

    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">Actions</h3>
            <a href="{{ route('church.expenses.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> Back to Expenses
            </a>
            @can('update', $expense)
                <a href="{{ route('church.expenses.edit', $expense) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> Edit Expense
                </a>
            @endcan
            @can('delete', $expense)
                <form method="POST" action="{{ route('church.expenses.destroy', $expense) }}" class="mt-2"
                    data-swal-confirm="Delete this expense?"
                    data-swal-delete
                    data-swal-confirm-text="Yes, delete">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> Delete Expense
                    </button>
                </form>
            @endcan
            @if($expense->budget)
                <a href="{{ route('church.budget.show', $expense->budget) }}" class="btn btn-outline-primary btn-block mt-2">
                    <i class="fa fa-briefcase"></i> View Budget
                </a>
            @endif
        </div>
    </div>
</div>
@endsection

