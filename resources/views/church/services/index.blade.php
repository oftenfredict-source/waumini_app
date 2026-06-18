@extends('layouts.church')

@section('title', 'Services')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-calendar"></i> Services</h1>
        <p>Sunday, Sunday School, mid-week, prayer, and extra church services</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Services</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search theme, preacher, venue..."
                value="{{ $filters['search'] ?? '' }}">
            <select name="service_type" class="form-control mr-2 mb-2">
                <option value="">All types</option>
                @foreach($serviceTypes as $type)
                    <option value="{{ $type->value }}" @selected(($filters['service_type'] ?? '') === $type->value)>
                        {{ $type->label() }}
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
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> Search</button>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        @can('create', \App\Models\ChurchService::class)
            <a href="{{ route('church.services.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> Create Service
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
                        <th>Type</th>
                        <th>Theme</th>
                        <th>Preacher</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th width="130">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <td>{{ $service->service_date->format('M d, Y') }}</td>
                            <td>
                                <span class="badge badge-{{ $service->service_type->badgeClass() }}">
                                    {{ $service->displayTitle() }}
                                </span>
                            </td>
                            <td>{{ $service->theme ?? '—' }}</td>
                            <td>{{ $service->preacher ?? '—' }}</td>
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
                            <td>
                                <span class="badge badge-{{ $service->status->badgeClass() }}">
                                    {{ $service->status->label() }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    @can('view', $service)
                                        <a href="{{ route('church.services.show', $service) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update', $service)
                                        <a href="{{ route('church.services.edit', $service) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $service)
                                        <form method="POST" action="{{ route('church.services.destroy', $service) }}" class="d-inline"
                                            data-swal-confirm="Delete this service on {{ $service->service_date->format('M d, Y') }}?"
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
                                No services found.
                                @can('create', \App\Models\ChurchService::class)
                                    <a href="{{ route('church.services.create') }}">Create a service</a>.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $services->links() }}
    </div>
</div>
@endsection
