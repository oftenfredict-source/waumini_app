@extends('layouts.church')

@section('title', 'Budget Details')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-briefcase"></i> Budget Details</h1>
        <p>{{ $budget->budget_name }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.budget.index') }}">Budget & Expenses</a></li>
        <li class="breadcrumb-item">Details</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">Budget Information</h3>
            <table class="table table-borderless table-sm">
                <tr>
                    <th width="170">Status</th>
                    <td>
                        <span class="badge badge-{{ $budget->status->badgeClass() }}">
                            {{ $budget->status->label() }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Approval</th>
                    <td>
                        <span class="badge badge-{{ $budget->approval_status->badgeClass() }}">
                            {{ $budget->approval_status->label() }}
                        </span>
                    </td>
                </tr>
                <tr><th>Type</th><td>{{ $budget->budget_type->label() }}</td></tr>
                <tr><th>Purpose</th><td>{{ $budget->purpose ?? '—' }}</td></tr>
                <tr><th>Primary Offering</th><td>{{ $budget->primary_offering_type ?? '—' }}</td></tr>
                <tr><th>Fiscal Year</th><td>{{ $budget->fiscal_year }}</td></tr>
                <tr><th>Period</th><td>{{ $budget->start_date->format('M d, Y') }} - {{ $budget->end_date->format('M d, Y') }}</td></tr>
                <tr><th>Total Budget</th><td><strong>TZS {{ number_format($budget->total_budget, 2) }}</strong></td></tr>
                <tr><th>Allocated</th><td>TZS {{ number_format($budget->offeringAllocations()->sum('allocated_amount'), 2) }}</td></tr>
                <tr><th>Spent (Paid Expenses)</th><td>TZS {{ number_format($budget->spent_amount, 2) }}</td></tr>
                <tr>
                    <th>Remaining</th>
                    <td>TZS {{ number_format($budget->remainingAmount(), 2) }}</td>
                </tr>
                <tr><th>Utilization</th><td>{{ $budget->utilizationPercentage() }}%</td></tr>
            </table>
        </div>

        <div class="tile mt-3">
            <h3 class="tile-title">Funding Allocations</h3>
            @forelse($budget->offeringAllocations as $allocation)
                <div class="mb-2 pb-2 border-bottom d-flex justify-content-between">
                    <div>
                        <strong>{{ $allocation->offering_type }}</strong>
                        @if($allocation->is_primary)
                            <span class="badge badge-primary ml-2">Primary</span>
                        @endif
                    </div>
                    <div>
                        <span>TZS {{ number_format($allocation->allocated_amount, 2) }}</span>
                        <span class="text-muted"> used: TZS {{ number_format($allocation->used_amount, 2) }}</span>
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0">No allocations yet.</p>
            @endforelse

            @can('allocateFunds', $budget)
                <hr>
                <h4 class="tile-title">Allocate Additional Funds</h4>
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
                    <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Allocate</button>
                </form>
            @endcan
        </div>

        <div class="tile mt-3">
            <h3 class="tile-title">Line Items</h3>
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Amount</th>
                            <th>Responsible</th>
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
                            <tr><td colspan="3" class="text-center text-muted py-3">No line items.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ route('church.budget.edit', $budget) }}" class="btn btn-outline-primary btn-sm mt-2">Edit Line Items</a>
        </div>

        <div class="tile mt-3">
            <h3 class="tile-title">Expenses (this budget)</h3>
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Name</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th width="140">Actions</th>
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
                                    <a href="{{ route('church.expenses.show', $expense) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No expenses recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @can('create', \App\Models\Expense::class)
                @if($budget->approval_status === \App\Enums\FinancialApprovalStatus::Approved)
                    <a href="{{ route('church.expenses.create', ['budget_id' => $budget->id]) }}" class="btn btn-primary btn-sm mt-2">
                        <i class="fa fa-plus"></i> Record Expense
                    </a>
                @endif
            @endcan
        </div>
    </div>

    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">Actions</h3>
            <a href="{{ route('church.budget.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> Back to Budgets
            </a>
            @can('update', $budget)
                <a href="{{ route('church.budget.edit', $budget) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> Edit Budget
                </a>
            @endcan
            @can('delete', $budget)
                <form method="POST" action="{{ route('church.budget.destroy', $budget) }}" class="mt-2"
                    data-swal-confirm="Delete this budget?"
                    data-swal-delete
                    data-swal-confirm-text="Yes, delete">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> Delete Budget
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection

