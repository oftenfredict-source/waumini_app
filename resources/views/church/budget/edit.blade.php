@extends('layouts.church')

@section('title', 'Edit Budget')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-pencil"></i> Edit Budget</h1>
        <p>{{ $budget->budget_name }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.budget.index') }}">Budget & Expenses</a></li>
        <li class="breadcrumb-item">Edit</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.budget.update', $budget) }}">
        @csrf
        @method('PUT')
        @include('church.budget._form', [
            'budget' => $budget,
            'budgetTypes' => $budgetTypes,
            'offeringTypes' => $offeringTypes,
            'statuses' => $statuses,
            'existingAllocations' => $existingAllocations,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Budget</button>
            <a href="{{ route('church.budget.show', $budget) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

