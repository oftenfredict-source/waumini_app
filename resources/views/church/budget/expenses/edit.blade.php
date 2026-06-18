@extends('layouts.church')

@section('title', 'Edit Expense')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-pencil"></i> Edit Expense</h1>
        <p>{{ $expense->expense_name }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.budget.index') }}">Budget & Expenses</a></li>
        <li class="breadcrumb-item">Expenses</li>
        <li class="breadcrumb-item">Edit</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.expenses.update', $expense) }}">
        @csrf
        @method('PUT')
        @include('church.budget.expenses._form', [
            'expense' => $expense,
            'budgets' => $budgets,
            'expenseCategories' => $expenseCategories,
            'paymentMethods' => $paymentMethods,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Expense</button>
            <a href="{{ route('church.expenses.show', $expense) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

