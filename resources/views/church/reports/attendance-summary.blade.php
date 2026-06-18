@extends('layouts.church')

@section('title', 'Attendance Summary')

@include('church.reports.partials.styles')

@section('content')
@include('church.reports.partials.nav')

<div class="report-hero">
    <h2><i class="fa fa-check-square-o"></i> Attendance Summary</h2>
    <p class="lead">{{ $start->format('M d, Y') }} – {{ $end->format('M d, Y') }}</p>
</div>

@include('church.reports.partials.date-filter')

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Total Records</div>
            <div class="report-stat-value">{{ number_format($report['total_records']) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Members</div>
            <div class="report-stat-value">{{ number_format($report['members']) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Children</div>
            <div class="report-stat-value">{{ number_format($report['children']) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Avg / Service</div>
            <div class="report-stat-value">{{ $report['average_per_service'] }}</div>
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title">Attendance by Month</h3>
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>Month</th><th class="text-right">Records</th></tr></thead>
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
            <h3 class="tile-title">Recent Services</h3>
            @if($report['services']->isNotEmpty())
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Date</th><th>Service</th><th class="text-right">Attendance</th></tr></thead>
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
                <p class="text-muted mb-0">No services in this period.</p>
            @endif
        </div>
    </div>
</div>
@endsection
