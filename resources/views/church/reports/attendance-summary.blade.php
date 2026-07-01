@extends('layouts.church')

@section('title', __('reports.attendance_summary'))

@include('church.reports.partials.styles')

@section('content')
@include('church.reports.partials.nav')

<div class="report-hero">
    <h2><i class="fa fa-check-square-o"></i> {{ __('reports.attendance_summary') }}</h2>
    <p class="lead">{{ $start->format('M d, Y') }} – {{ $end->format('M d, Y') }}</p>
</div>

@include('church.reports.partials.date-filter')

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">{{ __('reports.total_records') }}</div>
            <div class="report-stat-value">{{ number_format($report['total_records']) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">{{ __('pages.shared.members') }}</div>
            <div class="report-stat-value">{{ number_format($report['members']) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">{{ __('reports.children') }}</div>
            <div class="report-stat-value">{{ number_format($report['children']) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">{{ __('reports.avg_per_service') }}</div>
            <div class="report-stat-value">{{ $report['average_per_service'] }}</div>
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('reports.attendance_by_month') }}</h3>
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>{{ __('reports.month') }}</th><th class="text-right">{{ __('reports.records') }}</th></tr></thead>
                <tbody>
                    @foreach($report['by_month'] as $row)
                        <tr><td>{{ $row['month'] }}</td><td class="text-right">{{ $row['count'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('reports.recent_services') }}</h3>
            @if($report['services']->isNotEmpty())
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>{{ __('common.date') }}</th><th>{{ __('reports.service') }}</th><th class="text-right">{{ __('reports.attendance') }}</th></tr></thead>
                    <tbody>
                        @foreach($report['services'] as $row)
                            <tr>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ $row['title'] }}</td>
                                <td class="text-right">{{ $row['attendance'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted mb-0">{{ __('reports.no_services') }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
