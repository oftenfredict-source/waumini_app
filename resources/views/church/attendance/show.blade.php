@extends('layouts.church')

@section('title', __('pages.attendance.show_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-check-square-o',
    'title' => __('pages.attendance.show_title'),
    'subtitle' => $sourceLabel,
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.attendance'), 'route' => 'church.attendance.index'],
        ['label' => __('pages.shared.breadcrumb_details')],
    ],
])

<div class="row mb-3">
    <div class="col-md-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-users fa-3x"></i>
            <div class="info"><h4>{{ __('pages.shared.members') }}</h4><p><b>{{ $summary['members_count'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-child fa-3x"></i>
            <div class="info"><h4>{{ __('pages.shared.children') }}</h4><p><b>{{ $summary['children_count'] }}</b></p>
                @if($attendanceMode === 'sunday_school')
                    <small>{{ __('pages.shared.sunday_school') }}</small>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-user-plus fa-3x"></i>
            <div class="info"><h4>{{ __('pages.shared.guests') }}</h4><p><b>{{ $summary['guests_count'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-check fa-3x"></i>
            <div class="info"><h4>{{ __('common.total') }}</h4><p><b>{{ $summary['total_count'] }}</b></p></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.shared.attendees') }}</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>{{ __('common.name') }}</th>
                            <th>{{ __('common.type') }}</th>
                            <th>{{ __('pages.shared.recorded_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary['records'] as $record)
                            <tr>
                                <td>{{ $record->attendeeName() }}</td>
                                <td>
                                    @if($record->member_id)
                                        {{ __('pages.attendance.attendee_type_member') }}
                                    @elseif($attendanceMode === 'sunday_school')
                                        {{ __('pages.attendance.attendee_type_sunday_school') }}
                                    @elseif($record->dependant && $record->dependant->shouldAttendMainService())
                                        {{ __('pages.attendance.attendee_type_teenager') }}
                                    @else
                                        {{ __('pages.attendance.attendee_type_child') }}
                                    @endif
                                </td>
                                <td>{{ $record->attended_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">{{ __('pages.attendance.no_records') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($summary['records']->first()?->notes)
                <p class="mt-3 mb-0"><strong>{{ __('pages.shared.notes') }}:</strong> {{ $summary['records']->first()->notes }}</p>
            @endif
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('common.actions') }}</h3>
            <a href="{{ route('church.attendance.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> {{ __('pages.attendance.back_to') }}
            </a>
            @can('create', \App\Models\AttendanceRecord::class)
                <a href="{{ route('church.attendance.create', ['source_type' => $sourceType->value, 'source_id' => $sourceId]) }}"
                    class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> {{ __('pages.attendance.edit_attendance') }}
                </a>
            @endcan
        </div>
    </div>
</div>
@endsection
