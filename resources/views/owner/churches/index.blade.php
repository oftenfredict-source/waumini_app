@extends('layouts.owner')

@section('title', 'Churches')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-building"></i> Churches</h1>
        <p>Manage all registered churches</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">Overview</a></li>
        <li class="breadcrumb-item">Churches</li>
    </ul>
</div>

<div class="tile">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search..." value="{{ $filters['search'] ?? '' }}">
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> Filter</button>
        </form>
        @can('create', App\Models\Church::class)
            <a href="{{ route('owner.churches.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> Add Church
            </a>
        @endcan
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>Church</th>
                    <th>Subdomain</th>
                    <th>Email</th>
                    <th>Package</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($churches as $church)
                    <tr>
                        <td>
                            <strong>{{ $church->name }}</strong><br>
                            <small class="text-muted">{{ $church->pastor_name }}</small>
                        </td>
                        <td><code>{{ $church->slug }}</code></td>
                        <td>{{ $church->email }}</td>
                        <td>{{ $church->activeSubscription?->package?->name ?? '—' }}</td>
                        <td>@include('owner.components.status-badge', ['status' => $church->status])</td>
                        <td>{{ $church->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('owner.churches.show', $church) }}" class="btn btn-sm btn-info" title="View"><i class="fa fa-eye"></i></a>
                            <a href="{{ route('owner.churches.edit', $church) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fa fa-edit"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No churches found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $churches->links() }}
</div>
@endsection
