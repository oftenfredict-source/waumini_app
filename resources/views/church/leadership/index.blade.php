@extends('layouts.church')

@section('title', 'Leaders')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-star"></i> Leadership</h1>
        <p>View church leaders and their positions</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Leadership</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search member name or number..."
                value="{{ $filters['search'] ?? '' }}">
            <select name="position" class="form-control mr-2 mb-2">
                <option value="">All positions</option>
                @foreach($positions as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['position'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">All statuses</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> Filter</button>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        @can('create', \App\Models\Leader::class)
            <a href="{{ route('church.leadership.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> Assign Leadership
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
                        <th>Member</th>
                        <th>Member #</th>
                        <th>Position</th>
                        <th>Appointed</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaders as $leader)
                        <tr>
                            <td>{{ $leader->member?->full_name ?? '—' }}</td>
                            <td><code>{{ $leader->member?->member_number ?? '—' }}</code></td>
                            <td>{{ $leader->positionLabel() }}</td>
                            <td>{{ $leader->appointment_date->format('M d, Y') }}</td>
                            <td>{{ $leader->end_date?->format('M d, Y') ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $leader->isCurrentlyActive() ? 'success' : 'secondary' }}">
                                    {{ $leader->isCurrentlyActive() ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('church.leadership.show', $leader) }}" class="btn btn-sm btn-info">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No leadership assignments found.
                                @can('create', \App\Models\Leader::class)
                                    <a href="{{ route('church.leadership.create') }}">Assign leadership</a>.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $leaders->links() }}
    </div>
</div>
@endsection
