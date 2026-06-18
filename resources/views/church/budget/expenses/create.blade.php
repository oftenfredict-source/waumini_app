@extends('layouts.church')

@section('title', 'Record Expense')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> Record Expense</h1>
        <p>Submit an expense for approval.</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.budget.index') }}">Budget & Expenses</a></li>
        <li class="breadcrumb-item">Expenses</li>
        <li class="breadcrumb-item">Record</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.expenses.store') }}">
        @csrf
        @include('church.budget.expenses._form', [
            'expense' => null,
            'budgets' => $budgets,
            'expenseCategories' => $expenseCategories,
            'paymentMethods' => $paymentMethods,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Expense</button>
            <a href="{{ route('church.expenses.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

