@extends('layouts.church')

@section('title', 'Bereavements')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-heart"></i> Bereavements</h1>
        <p>Track bereavement events and member contributions</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Bereavements</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search deceased, family, departments..."
                value="{{ $filters['search'] ?? '' }}">
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
        @can('create', \App\Models\BereavementEvent::class)
            <a href="{{ route('church.bereavements.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> Create Bereavement
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
                        <th>Incident Date</th>
                        <th>Deceased / Affected</th>
                        <th>Contribution Period</th>
                        <th>Total Raised</th>
                        <th>Contributors</th>
                        <th>Status</th>
                        <th width="130">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td>{{ $event->incident_date->format('M d, Y') }}</td>
                            <td>
                                {{ $event->deceased_name }}
                                @if($event->affectedMember)
                                    <br><small class="text-muted">Member: {{ $event->affectedMember->full_name }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $event->contribution_start_date->format('M d') }}
                                – {{ $event->contribution_end_date->format('M d, Y') }}
                            </td>
                            <td>TZS {{ number_format($event->total_raised ?? 0, 2) }}</td>
                            <td>{{ $event->contributors_count ?? 0 }}</td>
                            <td>
                                <span class="badge badge-{{ $event->status->badgeClass() }}">
                                    {{ $event->status->label() }}
                                </span>
                                @if($event->isExpired())
                                    <span class="badge badge-warning">Expired</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    @can('view', $event)
                                        <a href="{{ route('church.bereavements.show', $event) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update', $event)
                                        <a href="{{ route('church.bereavements.edit', $event) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $event)
                                        <form method="POST" action="{{ route('church.bereavements.destroy', $event) }}" class="d-inline"
                                            data-swal-confirm="Delete bereavement for {{ $event->deceased_name }}?"
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
                                No bereavement events found.
                                @can('create', \App\Models\BereavementEvent::class)
                                    <a href="{{ route('church.bereavements.create') }}">Create one</a>.
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
