@extends('layouts.church')

@section('title', __('reports.title'))

@include('church.reports.partials.styles')

@section('content')
@php $currency = $summary['currency']; $financial = $summary['financial']; @endphp

<div class="report-hero">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h2><i class="fa fa-file-text"></i> {{ __('reports.heading') }}</h2>
            <p class="lead">{{ __('reports.subtitle', ['church' => $church->name]) }}</p>
        </div>
        <div class="col-lg-4 text-lg-right mt-3 mt-lg-0 no-print">
            <a href="{{ route('church.analytics.index') }}" class="btn btn-light mr-2">
                <i class="fa fa-line-chart"></i> {{ __('reports.analytics') }}
            </a>
            <button type="button" class="btn btn-outline-light" onclick="window.print()">
                <i class="fa fa-print"></i> {{ __('common.print') }}
            </button>
        </div>
    </div>
</div>

@include('church.reports.partials.date-filter')

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card">
            <div class="card-body">
                <div class="report-stat-label">{{ __('reports.total_members') }}</div>
                <div class="report-stat-value text-primary">{{ number_format($summary['total_members']) }}</div>
                <small class="text-muted">{{ __('reports.active_label', ['count' => number_format($summary['active_members'])]) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card">
            <div class="card-body">
                <div class="report-stat-label">{{ __('reports.income_period') }}</div>
                <div class="report-stat-value text-success">{{ $currency }} {{ number_format($financial['income'], 0) }}</div>
                <small class="text-muted">{{ __('reports.transactions', ['count' => number_format($financial['transaction_count'])]) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card">
            <div class="card-body">
                <div class="report-stat-label">{{ __('reports.net_period') }}</div>
                <div class="report-stat-value {{ $financial['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $currency }} {{ number_format($financial['net'], 0) }}
                </div>
                <small class="text-muted">{{ __('reports.income_minus_expenses') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card">
            <div class="card-body">
                <div class="report-stat-label">{{ __('reports.attendance_period') }}</div>
                <div class="report-stat-value text-info">{{ number_format($summary['attendance_count']) }}</div>
                <small class="text-muted">{{ __('reports.active_leaders', ['count' => number_format($summary['active_leaders'])]) }}</small>
            </div>
        </div>
    </div>
</div>

<div class="tile">
    <h3 class="tile-title">{{ __('reports.available_reports') }}</h3>
    <div class="row">
        @php
            $reports = [
                ['route' => 'church.reports.overview', 'icon' => 'fa-dashboard', 'title' => __('reports.overview'), 'desc' => __('reports.overview_desc')],
                ['route' => 'church.reports.member-summary', 'icon' => 'fa-users', 'title' => __('reports.member_summary'), 'desc' => __('reports.member_summary_desc')],
                ['route' => 'church.reports.member-giving', 'icon' => 'fa-money', 'title' => __('reports.member_giving'), 'desc' => __('reports.member_giving_desc')],
                ['route' => 'church.reports.income-vs-expenditure', 'icon' => 'fa-balance-scale', 'title' => __('reports.income_vs_expenditure'), 'desc' => __('reports.income_vs_expenditure_desc')],
                ['route' => 'church.reports.offering-breakdown', 'icon' => 'fa-gift', 'title' => __('reports.offering_breakdown'), 'desc' => __('reports.offering_breakdown_desc')],
                ['route' => 'church.reports.budget-performance', 'icon' => 'fa-pie-chart', 'title' => __('reports.budget_performance'), 'desc' => __('reports.budget_performance_desc')],
                ['route' => 'church.reports.monthly-financial', 'icon' => 'fa-calendar', 'title' => __('reports.monthly_financial'), 'desc' => __('reports.monthly_financial_desc')],
                ['route' => 'church.reports.attendance-summary', 'icon' => 'fa-check-square-o', 'title' => __('reports.attendance_summary'), 'desc' => __('reports.attendance_summary_desc')],
                ['route' => 'church.reports.leadership', 'icon' => 'fa-star', 'title' => __('reports.leadership_report'), 'desc' => __('reports.leadership_report_desc')],
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
