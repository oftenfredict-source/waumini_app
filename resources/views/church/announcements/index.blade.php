@extends('layouts.church')

@section('title', 'Announcements')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-bullhorn"></i> Announcements</h1>
        <p>Church announcements and notices</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Announcements</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search title or content..."
                value="{{ $filters['search'] ?? '' }}">
            <select name="type" class="form-control mr-2 mb-2">
                <option value="">All types</option>
                @foreach($types as $type)
                    <option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>{{ $type->label() }}</option>
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
        @can('create', \App\Models\Announcement::class)
            <a href="{{ route('church.announcements.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> Create Announcement
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
                        <th>Title</th>
                        <th>Type</th>
                        <th>Audience</th>
                        <th>Period</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                        <tr>
                            <td>
                                @if($announcement->is_pinned)
                                    <i class="fa fa-thumb-tack text-warning" title="Pinned"></i>
                                @endif
                                {{ $announcement->title }}
                            </td>
                            <td>
                                <span class="badge badge-{{ $announcement->type->badgeClass() }}">
                                    {{ $announcement->type->label() }}
                                </span>
                            </td>
                            <td>{{ $announcement->target_type->label() }}</td>
                            <td>
                                {{ $announcement->start_date?->format('M d, Y') ?? 'Now' }}
                                —
                                {{ $announcement->end_date?->format('M d, Y') ?? 'Open' }}
                            </td>
                            <td>
                                <span class="badge badge-{{ $announcement->isCurrentlyActive() ? 'success' : 'secondary' }}">
                                    {{ $announcement->isCurrentlyActive() ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $announcement->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('church.announcements.show', $announcement) }}" class="btn btn-sm btn-info">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No announcements found.
                                @can('create', \App\Models\Announcement::class)
                                    <a href="{{ route('church.announcements.create') }}">Create one</a>.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $announcements->links() }}
    </div>
</div>
@endsection
