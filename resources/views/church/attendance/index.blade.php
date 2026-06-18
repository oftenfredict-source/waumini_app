@extends('layouts.church')

@section('title', 'Attendance')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-check-square-o"></i> Attendance</h1>
        <p>Record and view attendance for services and special events</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Attendance</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-calendar-check-o fa-3x"></i>
            <div class="info">
                <h4>Sessions Recorded</h4>
                <p><b>{{ $stats['recorded_sessions'] }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-users fa-3x"></i>
            <div class="info">
                <h4>Member Records</h4>
                <p><b>{{ $stats['total_members_marked'] }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-child fa-3x"></i>
            <div class="info">
                <h4>Child Records</h4>
                <p><b>{{ $stats['total_children_marked'] }}</b></p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search service or event..."
                value="{{ $filters['search'] ?? '' }}">
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> Search</button>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        @can('create', \App\Models\AttendanceRecord::class)
            <a href="{{ route('church.attendance.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> Record Attendance
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
                        <th>Service / Event</th>
                        <th>Members</th>
                        <th>Children</th>
                        <th>Guests</th>
                        <th>Total</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        <tr>
                            <td>{{ $session['date']->format('M d, Y') }}</td>
                            <td>{{ $session['label'] }}</td>
                            <td>{{ $session['members_count'] }}</td>
                            <td>{{ $session['children_count'] }}</td>
                            <td>{{ $session['guests_count'] }}</td>
                            <td>
                                @if($session['has_attendance'])
                                    <span class="badge badge-success">{{ $session['total_count'] }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    @if($session['has_attendance'])
                                        <a href="{{ route('church.attendance.show', ['source_type' => $session['source_type'], 'source_id' => $session['source_id']]) }}"
                                            class="btn btn-sm btn-info" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endif
                                    @can('create', \App\Models\AttendanceRecord::class)
                                        <a href="{{ route('church.attendance.create', ['source_type' => $session['source_type'], 'source_id' => $session['source_id']]) }}"
                                            class="btn btn-sm btn-primary" title="{{ $session['has_attendance'] ? 'Edit' : 'Record' }}">
                                            <i class="fa fa-{{ $session['has_attendance'] ? 'pencil' : 'plus' }}"></i>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No services or events found. Create a service or event first, then record attendance.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
