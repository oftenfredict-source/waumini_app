@extends('layouts.church')

@section('title', 'Create Special Event')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> Create Special Event</h1>
        <p>Schedule a conference, wedding, crusade, or other church event</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.special-events.index') }}">Special Events</a></li>
        <li class="breadcrumb-item">Create</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.special-events.store') }}">
        @csrf
        @include('church.special-events._form', [
            'categories' => $categories,
            'statuses' => $statuses,
        ])
        <div class="tile-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Event</button>
            <a href="{{ route('church.special-events.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@include('church.special-events._form-scripts')
@endpush
