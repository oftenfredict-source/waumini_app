@extends('layouts.church')

@section('title', 'Edit Pledge')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-pencil"></i> Edit Pledge</h1>
        <p>{{ $pledge->member?->full_name ?? 'Pledge record' }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.pledges.index') }}">Pledges</a></li>
        <li class="breadcrumb-item">Edit</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.pledges.update', $pledge) }}">
        @csrf
        @method('PUT')
        @include('church.pledges._form', [
            'pledge' => $pledge,
            'members' => $members,
            'pledgeTypes' => $pledgeTypes,
            'frequencies' => $frequencies,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Pledge</button>
            <a href="{{ route('church.pledges.show', $pledge) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@include('church.pledges._form-scripts')
@endpush
