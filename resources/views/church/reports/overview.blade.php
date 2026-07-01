@extends('layouts.church')

@section('title', __('reports.reports_overview'))

@include('church.reports.partials.styles')

@section('content')
@php $currency = $report['currency']; $financial = $report['financial']; @endphp

@include('church.reports.partials.nav')

<div class="report-hero">
    <h2><i class="fa fa-dashboard"></i> {{ __('reports.reports_overview') }}</h2>
    <p class="lead">{{ $start->format('M d, Y') }} – {{ $end->format('M d, Y') }}</p>
</div>

@include('church.reports.partials.date-filter')

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">{{ __('reports.total_members') }}</div>
            <div class="report-stat-value">{{ number_format($report['total_members']) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">{{ __('reports.new_30_days') }}</div>
            <div class="report-stat-value">{{ number_format($report['new_members_30d']) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">{{ __('reports.total_giving') }}</div>
            <div class="report-stat-value text-success">{{ $currency }} {{ number_format($financial['income'], 0) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">{{ __('common.total') }}</div>
            <div class="report-stat-value">{{ number_format($financial['transaction_count']) }}</div>
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('reports.top_contributors') }}</h3>
            @if($report['top_contributors']->isNotEmpty())
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>#</th><th>{{ __('common.member') }}</th><th class="text-right">{{ __('common.total') }} ({{ $currency }})</th></tr></thead>
                    <tbody>
                        @foreach($report['top_contributors'] as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <a href="{{ route('church.reports.member-giving', ['member_id' => $row['member_id'], 'start_date' => $start->format('Y-m-d'), 'end_date' => $end->format('Y-m-d')]) }}">
                                        {{ $row['full_name'] }}
                                    </a>
                                </td>
                                <td class="text-right">{{ number_format($row['total'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted mb-0">{{ __('reports.no_contributions') }}</p>
            @endif
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('reports.income_breakdown') }}</h3>
            <table class="table table-sm mb-0">
                <tr><th>{{ __('reports.tithes') }}</th><td class="text-right">{{ $currency }} {{ number_format($financial['tithes'], 2) }}</td></tr>
                <tr><th>{{ __('reports.offerings') }}</th><td class="text-right">{{ $currency }} {{ number_format($financial['offerings'], 2) }}</td></tr>
                <tr><th>{{ __('reports.pledge_payments') }}</th><td class="text-right">{{ $currency }} {{ number_format($financial['pledges'], 2) }}</td></tr>
                <tr><th>{{ __('reports.bereavements') }}</th><td class="text-right">{{ $currency }} {{ number_format($financial['bereavements'], 2) }}</td></tr>
                <tr class="font-weight-bold"><th>{{ __('reports.expenses') }}</th><td class="text-right text-danger">{{ $currency }} {{ number_format($financial['expenses'], 2) }}</td></tr>
                <tr class="font-weight-bold"><th>{{ __('reports.net') }}</th><td class="text-right">{{ $currency }} {{ number_format($financial['net'], 2) }}</td></tr>
            </table>
        </div>
    </div>
</div>

<div class="tile">
    <h3 class="tile-title">{{ __('reports.offerings_by_type') }}</h3>
    @if($report['offering_types']->isNotEmpty())
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>{{ __('common.type') }}</th><th>{{ __('reports.count') }}</th><th class="text-right">{{ __('reports.amount') }} ({{ $currency }})</th></tr></thead>
            <tbody>
                @foreach($report['offering_types'] as $row)
                    <tr>
                        <td>{{ $row['type'] }}</td>
                        <td>{{ $row['count'] }}</td>
                        <td class="text-right">{{ number_format($row['total_amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-muted mb-0">{{ __('reports.no_offerings') }}</p>
    @endif
</div>
@endsection
