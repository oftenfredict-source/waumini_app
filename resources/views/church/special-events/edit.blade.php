@extends('layouts.church')

@section('title', 'Edit Special Event')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-pencil"></i> Edit Special Event</h1>
        <p>{{ $event->title }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.special-events.index') }}">Special Events</a></li>
        <li class="breadcrumb-item">Edit</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.special-events.update', $event) }}">
        @csrf
        @method('PUT')
        @include('church.special-events._form', [
            'event' => $event,
            'categories' => $categories,
            'statuses' => $statuses,
        ])
        <div class="tile-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Event</button>
            <a href="{{ route('church.special-events.show', $event) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@include('church.special-events._form-scripts')
@endpush
