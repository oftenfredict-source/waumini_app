@extends('layouts.church')

@section('title', 'Record Pledge')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> Record Pledge</h1>
        <p>Create a new member pledge commitment</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.pledges.index') }}">Pledges</a></li>
        <li class="breadcrumb-item">Record</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.pledges.store') }}">
        @csrf
        @include('church.pledges._form', [
            'members' => $members,
            'pledgeTypes' => $pledgeTypes,
            'frequencies' => $frequencies,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Pledge</button>
            <a href="{{ route('church.pledges.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@include('church.pledges._form-scripts')
@endpush
