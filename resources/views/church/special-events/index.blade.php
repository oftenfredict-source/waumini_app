@extends('layouts.church')

@section('title', 'Special Events')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-star"></i> Special Events</h1>
        <p>Conferences, weddings, crusades, and other church events</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Special Events</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search title, speaker, venue..."
                value="{{ $filters['search'] ?? '' }}">
            <select name="category" class="form-control mr-2 mb-2">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->value }}" @selected(($filters['category'] ?? '') === $category->value)>
                        {{ $category->label() }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="from" class="form-control mr-2 mb-2" value="{{ $filters['from'] ?? '' }}" title="From date">
            <input type="date" name="to" class="form-control mr-2 mb-2" value="{{ $filters['to'] ?? '' }}" title="To date">
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> Search</button>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        @can('create', \App\Models\SpecialEvent::class)
            <a href="{{ route('church.special-events.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> Create Event
            </a>
        @endcan
    </div>
</div>

<div class="tile">
    <div class="tile-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Speaker</th>
                        <th>Venue</th>
                        <th>Status</th>
                        <th width="130">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td>{{ $event->event_date->format('M d, Y') }}</td>
                            <td>{{ $event->title }}</td>
                            <td>
                                <span class="badge badge-{{ $event->category->badgeClass() }}">
                                    {{ $event->categoryLabel() }}
                                </span>
                            </td>
                            <td>{{ $event->speaker ?? '—' }}</td>
                            <td>{{ $event->venue ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $event->status->badgeClass() }}">
                                    {{ $event->status->label() }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    @can('view', $event)
                                        <a href="{{ route('church.special-events.show', $event) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update', $event)
                                        <a href="{{ route('church.special-events.edit', $event) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $event)
                                        <form method="POST" action="{{ route('church.special-events.destroy', $event) }}" class="d-inline"
                                            data-swal-confirm="Delete {{ $event->title }}?"
                                            data-swal-delete
                                            data-swal-confirm-text="Yes, delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No special events found.
                                @can('create', \App\Models\SpecialEvent::class)
                                    <a href="{{ route('church.special-events.create') }}">Create an event</a>.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $events->links() }}
    </div>
</div>
@endsection
