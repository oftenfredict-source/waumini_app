@extends('layouts.church')

@section('title', 'Attendance Details')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-check-square-o"></i> Attendance Details</h1>
        <p>{{ $sourceLabel }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.attendance.index') }}">Attendance</a></li>
        <li class="breadcrumb-item">Details</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-users fa-3x"></i>
            <div class="info"><h4>Members</h4><p><b>{{ $summary['members_count'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-child fa-3x"></i>
            <div class="info"><h4>Children</h4><p><b>{{ $summary['children_count'] }}</b></p>
                @if($attendanceMode === 'sunday_school')
                    <small>Sunday School</small>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-user-plus fa-3x"></i>
            <div class="info"><h4>Guests</h4><p><b>{{ $summary['guests_count'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-check fa-3x"></i>
            <div class="info"><h4>Total</h4><p><b>{{ $summary['total_count'] }}</b></p></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">Attendees</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Recorded At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary['records'] as $record)
                            <tr>
                                <td>{{ $record->attendeeName() }}</td>
                                <td>
                                    @if($record->member_id)
                                        Member
                                    @elseif($attendanceMode === 'sunday_school')
                                        Sunday School
                                    @elseif($record->dependant && $record->dependant->shouldAttendMainService())
                                        Teenager
                                    @else
                                        Child
                                    @endif
                                </td>
                                <td>{{ $record->attended_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No member or child attendance recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($summary['records']->first()?->notes)
                <p class="mt-3 mb-0"><strong>Notes:</strong> {{ $summary['records']->first()->notes }}</p>
            @endif
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">Actions</h3>
            <a href="{{ route('church.attendance.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> Back to Attendance
            </a>
            @can('create', \App\Models\AttendanceRecord::class)
                <a href="{{ route('church.attendance.create', ['source_type' => $sourceType->value, 'source_id' => $sourceId]) }}"
                    class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> Edit Attendance
                </a>
            @endcan
        </div>
    </div>
</div>
@endsection
