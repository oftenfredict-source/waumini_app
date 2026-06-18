@extends('layouts.church')

@section('title', 'Create Budget')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> Create Budget</h1>
        <p>Submit a church budget for approval.</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.budget.index') }}">Budget & Expenses</a></li>
        <li class="breadcrumb-item">Create</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.budget.store') }}">
        @csrf
        @include('church.budget._form', [
            'budget' => null,
            'budgetTypes' => $budgetTypes,
            'offeringTypes' => $offeringTypes,
            'statuses' => $statuses,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Budget</button>
            <a href="{{ route('church.budget.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

