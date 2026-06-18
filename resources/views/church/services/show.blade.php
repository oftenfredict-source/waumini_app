@extends('layouts.church')

@section('title', $service->displayTitle())

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-calendar"></i> {{ $service->displayTitle() }}</h1>
        <p>{{ $service->service_date->format('l, M d, Y') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.services.index') }}">Services</a></li>
        <li class="breadcrumb-item">{{ $service->displayTitle() }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">Service Details</h3>
            <table class="table table-borderless table-sm">
                <tr>
                    <th width="180">Type</th>
                    <td>
                        <span class="badge badge-{{ $service->service_type->badgeClass() }}">
                            {{ $service->service_type->label() }}
                        </span>
                    </td>
                </tr>
                @if($service->title)
                    <tr><th>Title</th><td>{{ $service->title }}</td></tr>
                @endif
                <tr><th>Date</th><td>{{ $service->service_date->format('M d, Y') }}</td></tr>
                <tr>
                    <th>Time</th>
                    <td>
                        @if($service->start_time)
                            {{ \Illuminate\Support\Str::of($service->start_time)->substr(0, 5) }}
                            @if($service->end_time)
                                – {{ \Illuminate\Support\Str::of($service->end_time)->substr(0, 5) }}
                            @endif
                        @else
                            —
                        @endif
                    </td>
                </tr>
                <tr><th>Theme</th><td>{{ $service->theme ?? '—' }}</td></tr>
                <tr><th>Preacher / Speaker</th><td>{{ $service->preacher ?? '—' }}</td></tr>
                <tr><th>Venue</th><td>{{ $service->venue ?? '—' }}</td></tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <span class="badge badge-{{ $service->status->badgeClass() }}">
                            {{ $service->status->label() }}
                        </span>
                    </td>
                </tr>
                <tr><th>Notes</th><td>{{ $service->notes ?? '—' }}</td></tr>
                <tr><th>Created By</th><td>{{ $service->creator?->name ?? '—' }}</td></tr>
                <tr><th>Recorded On</th><td>{{ $service->created_at->format('M d, Y H:i') }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">Actions</h3>
            <a href="{{ route('church.services.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> Back to Services
            </a>
            @can('update', $service)
                <a href="{{ route('church.services.edit', $service) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> Edit Service
                </a>
            @endcan
            @can('delete', $service)
                <form method="POST" action="{{ route('church.services.destroy', $service) }}" class="mt-2"
                    data-swal-confirm="Delete this service? This cannot be undone."
                    data-swal-delete
                    data-swal-confirm-text="Yes, delete">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> Delete Service
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
