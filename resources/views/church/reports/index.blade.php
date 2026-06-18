@extends('layouts.church')

@section('title', 'All Reports')

@include('church.reports.partials.styles')

@section('content')
@php $currency = $summary['currency']; $financial = $summary['financial']; @endphp

<div class="report-hero">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h2><i class="fa fa-file-text"></i> All Reports</h2>
            <p class="lead">Generate and review church reports for {{ $church->name }}.</p>
        </div>
        <div class="col-lg-4 text-lg-right mt-3 mt-lg-0 no-print">
            <a href="{{ route('church.analytics.index') }}" class="btn btn-light mr-2">
                <i class="fa fa-line-chart"></i> Analytics
            </a>
            <button type="button" class="btn btn-outline-light" onclick="window.print()">
                <i class="fa fa-print"></i> Print
            </button>
        </div>
    </div>
</div>

@include('church.reports.partials.date-filter')

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card">
            <div class="card-body">
                <div class="report-stat-label">Total Members</div>
                <div class="report-stat-value text-primary">{{ number_format($summary['total_members']) }}</div>
                <small class="text-muted">{{ number_format($summary['active_members']) }} active</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card">
            <div class="card-body">
                <div class="report-stat-label">Income (Period)</div>
                <div class="report-stat-value text-success">{{ $currency }} {{ number_format($financial['income'], 0) }}</div>
                <small class="text-muted">{{ number_format($financial['transaction_count']) }} transactions</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card">
            <div class="card-body">
                <div class="report-stat-label">Net (Period)</div>
                <div class="report-stat-value {{ $financial['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $currency }} {{ number_format($financial['net'], 0) }}
                </div>
                <small class="text-muted">Income minus expenses</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card">
            <div class="card-body">
                <div class="report-stat-label">Attendance (Period)</div>
                <div class="report-stat-value text-info">{{ number_format($summary['attendance_count']) }}</div>
                <small class="text-muted">{{ number_format($summary['active_leaders']) }} active leaders</small>
            </div>
        </div>
    </div>
</div>

<div class="tile">
    <h3 class="tile-title">Available Reports</h3>
    <div class="row">
        @php
            $reports = [
                ['route' => 'church.reports.overview', 'icon' => 'fa-dashboard', 'title' => 'Overview', 'desc' => 'Members, giving, and finance snapshot'],
                ['route' => 'church.reports.member-summary', 'icon' => 'fa-users', 'title' => 'Member Summary', 'desc' => 'Membership statistics and demographics'],
                ['route' => 'church.reports.member-giving', 'icon' => 'fa-money', 'title' => 'Member Giving', 'desc' => 'Contributions by member for a period'],
                ['route' => 'church.reports.income-vs-expenditure', 'icon' => 'fa-balance-scale', 'title' => 'Income vs Expenditure', 'desc' => 'Income and expense comparison'],
                ['route' => 'church.reports.offering-breakdown', 'icon' => 'fa-gift', 'title' => 'Offering Breakdown', 'desc' => 'Offerings grouped by type'],
                ['route' => 'church.reports.budget-performance', 'icon' => 'fa-pie-chart', 'title' => 'Budget Performance', 'desc' => 'Budget utilization and spending'],
                ['route' => 'church.reports.monthly-financial', 'icon' => 'fa-calendar', 'title' => 'Monthly Financial', 'desc' => 'Week-by-week monthly finance report'],
                ['route' => 'church.reports.attendance-summary', 'icon' => 'fa-check-square-o', 'title' => 'Attendance Summary', 'desc' => 'Service attendance records'],
                ['route' => 'church.reports.leadership', 'icon' => 'fa-star', 'title' => 'Leadership Report', 'desc' => 'Church leadership roster'],
            ];
        @endphp
        @foreach($reports as $item)
            <div class="col-md-4 col-sm-6 mb-3">
                <a href="{{ route($item['route'], request()->only(['start_date', 'end_date'])) }}" class="report-card-link">
                    <i class="fa {{ $item['icon'] }} d-block"></i>
                    <strong>{{ $item['title'] }}</strong>
                    <p class="text-muted small mb-0 mt-1">{{ $item['desc'] }}</p>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
