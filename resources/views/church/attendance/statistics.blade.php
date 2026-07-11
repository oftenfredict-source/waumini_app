@extends('layouts.church')

@section('title', __('pages.attendance.statistics_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-bar-chart',
    'title' => __('pages.attendance.statistics_title'),
    'subtitle' => __('pages.attendance.statistics_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.attendance'), 'route' => 'church.attendance.index'],
        ['label' => __('pages.attendance.statistics_title')],
    ],
])

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <label class="mr-2 mb-2">{{ __('pages.attendance.from') }}</label>
            <input type="date" name="start_date" class="form-control mr-2 mb-2" value="{{ $filters['start_date'] }}">
            <label class="mr-2 mb-2">{{ __('pages.attendance.to') }}</label>
            <input type="date" name="end_date" class="form-control mr-2 mb-2" value="{{ $filters['end_date'] }}">
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-filter"></i> {{ __('common.filter') }}</button>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        <a href="{{ route('church.attendance.index') }}" class="btn btn-secondary mb-2">
            <i class="fa fa-arrow-left"></i> {{ __('pages.attendance.back_to') }}
        </a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-calendar-check-o fa-3x"></i>
            <div class="info">
                <h4>{{ __('pages.attendance.stat_services') }}</h4>
                <p><b>{{ $report['totals']['services'] }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-users fa-3x"></i>
            <div class="info">
                <h4>{{ __('pages.attendance.stat_member_marks') }}</h4>
                <p><b>{{ $report['totals']['member_marks'] }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-child fa-3x"></i>
            <div class="info">
                <h4>{{ __('pages.attendance.stat_child_marks') }}</h4>
                <p><b>{{ $report['totals']['child_marks'] }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-line-chart fa-3x"></i>
            <div class="info">
                <h4>{{ __('pages.attendance.stat_avg_members') }}</h4>
                <p><b>{{ $report['totals']['average_members'] }}</b></p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.attendance.sunday_overview') }}</h3>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <strong>{{ __('pages.attendance.sunday_services') }}</strong>
                    <div>{{ $report['sunday_totals']['services'] }}</div>
                </div>
                <div class="col-md-3 mb-2">
                    <strong>{{ __('pages.attendance.avg_sunday_members') }}</strong>
                    <div>{{ $report['sunday_totals']['average_members'] }}</div>
                </div>
                <div class="col-md-3 mb-2">
                    <strong>{{ __('pages.attendance.active_members') }}</strong>
                    <div>{{ $report['sunday_totals']['active_members'] }}</div>
                </div>
                <div class="col-md-3 mb-2">
                    <strong>{{ __('pages.attendance.avg_sunday_rate') }}</strong>
                    <div>{{ $report['sunday_totals']['avg_rate'] }}%</div>
                </div>
            </div>
            <p class="text-muted mb-0 mt-2">
                <i class="fa fa-info-circle"></i> {{ __('pages.attendance.missed_sms_info') }}
            </p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7 mb-3">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.attendance.service_breakdown') }}</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('common.date') }}</th>
                            <th>{{ __('pages.shared.service_event') }}</th>
                            <th class="text-right">{{ __('pages.shared.members') }}</th>
                            <th class="text-right">{{ __('pages.shared.children') }}</th>
                            <th class="text-right">{{ __('pages.shared.guests') }}</th>
                            <th class="text-right">{{ __('pages.attendance.rate') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['services'] as $row)
                            <tr>
                                <td>{{ $row['date']->format('M d, Y') }}</td>
                                <td>
                                    {{ $row['title'] }}
                                    @if($row['is_sunday'])
                                        <span class="badge badge-primary">{{ __('pages.attendance.sunday_badge') }}</span>
                                    @endif
                                </td>
                                <td class="text-right">{{ $row['members_count'] }}</td>
                                <td class="text-right">{{ $row['children_count'] }}</td>
                                <td class="text-right">{{ $row['guests_count'] }}</td>
                                <td class="text-right">{{ $row['attendance_rate'] }}%</td>
                                <td>
                                    <a href="{{ route('church.attendance.show', [
                                        'source_type' => \App\Enums\AttendanceSourceType::ChurchService->value,
                                        'source_id' => $row['id'],
                                    ]) }}" class="btn btn-sm btn-info" title="{{ __('common.view') }}">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">{{ __('pages.attendance.no_stats') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5 mb-3">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.attendance.missing_recent_sundays') }}</h3>
            @if($report['recent_sundays']->isNotEmpty())
                <p class="text-muted small">
                    {{ __('pages.attendance.based_on_sundays', [
                        'dates' => $report['recent_sundays']->map(fn ($s) => $s->service_date->format('M d'))->implode(', '),
                    ]) }}
                </p>
            @endif
            <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('pages.shared.envelope') }}</th>
                            <th>{{ __('common.name') }}</th>
                            <th class="text-right">{{ __('pages.attendance.consecutive') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['missing_members'] as $member)
                            <tr class="{{ $member['consecutive_misses'] >= 3 ? 'table-danger' : ($member['consecutive_misses'] === 2 ? 'table-warning' : '') }}">
                                <td>{{ $member['envelope_number'] ?: '—' }}</td>
                                <td>
                                    {{ $member['full_name'] }}
                                    @if($member['consecutive_misses'] >= 3)
                                        <span class="badge badge-danger">{{ __('pages.attendance.sms_eligible') }}</span>
                                    @endif
                                </td>
                                <td class="text-right"><strong>{{ $member['consecutive_misses'] }}</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">{{ __('pages.attendance.no_missing_members') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
