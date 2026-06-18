@extends('layouts.church')

@section('title', $event->title)

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-star"></i> {{ $event->title }}</h1>
        <p>{{ $event->event_date->format('l, M d, Y') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.special-events.index') }}">Special Events</a></li>
        <li class="breadcrumb-item">{{ $event->title }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">Event Details</h3>
            <table class="table table-borderless table-sm">
                <tr>
                    <th width="180">Category</th>
                    <td>
                        <span class="badge badge-{{ $event->category->badgeClass() }}">
                            {{ $event->categoryLabel() }}
                        </span>
                    </td>
                </tr>
                <tr><th>Date</th><td>{{ $event->event_date->format('M d, Y') }}</td></tr>
                <tr>
                    <th>Time</th>
                    <td>
                        @if($event->start_time)
                            {{ \Illuminate\Support\Str::of($event->start_time)->substr(0, 5) }}
                            @if($event->end_time)
                                – {{ \Illuminate\Support\Str::of($event->end_time)->substr(0, 5) }}
                            @endif
                        @else
                            —
                        @endif
                    </td>
                </tr>
                <tr><th>Speaker / Guest</th><td>{{ $event->speaker ?? '—' }}</td></tr>
                <tr><th>Venue</th><td>{{ $event->venue ?? '—' }}</td></tr>
                <tr>
                    <th>Budget</th>
                    <td>{{ $event->budget_amount !== null ? 'TZS '.number_format($event->budget_amount, 2) : '—' }}</td>
                </tr>
                <tr><th>Expected Attendance</th><td>{{ $event->expected_attendance ?? '—' }}</td></tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <span class="badge badge-{{ $event->status->badgeClass() }}">
                            {{ $event->status->label() }}
                        </span>
                    </td>
                </tr>
                <tr><th>Description</th><td>{{ $event->description ?? '—' }}</td></tr>
                <tr><th>Notes</th><td>{{ $event->notes ?? '—' }}</td></tr>
                <tr><th>Created By</th><td>{{ $event->creator?->name ?? '—' }}</td></tr>
                <tr><th>Recorded On</th><td>{{ $event->created_at->format('M d, Y H:i') }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">Actions</h3>
            <a href="{{ route('church.special-events.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> Back to Events
            </a>
            @can('update', $event)
                <a href="{{ route('church.special-events.edit', $event) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> Edit Event
                </a>
            @endcan
            @can('delete', $event)
                <form method="POST" action="{{ route('church.special-events.destroy', $event) }}" class="mt-2"
                    data-swal-confirm="Delete this event? This cannot be undone."
                    data-swal-delete
                    data-swal-confirm-text="Yes, delete">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> Delete Event
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
