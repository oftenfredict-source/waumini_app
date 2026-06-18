@extends('layouts.church')

@section('title', 'Reports Overview')

@include('church.reports.partials.styles')

@section('content')
@php $currency = $report['currency']; $financial = $report['financial']; @endphp

@include('church.reports.partials.nav')

<div class="report-hero">
    <h2><i class="fa fa-dashboard"></i> Reports Overview</h2>
    <p class="lead">{{ $start->format('M d, Y') }} – {{ $end->format('M d, Y') }}</p>
</div>

@include('church.reports.partials.date-filter')

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Total Members</div>
            <div class="report-stat-value">{{ number_format($report['total_members']) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">New (30 days)</div>
            <div class="report-stat-value">{{ number_format($report['new_members_30d']) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Total Giving</div>
            <div class="report-stat-value text-success">{{ $currency }} {{ number_format($financial['income'], 0) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Transactions</div>
            <div class="report-stat-value">{{ number_format($financial['transaction_count']) }}</div>
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title">Top Contributors</h3>
            @if($report['top_contributors']->isNotEmpty())
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>#</th><th>Member</th><th class="text-right">Total ({{ $currency }})</th></tr></thead>
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
                <p class="text-muted mb-0">No contributions recorded for this period.</p>
            @endif
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title">Income Breakdown</h3>
            <table class="table table-sm mb-0">
                <tr><th>Tithes</th><td class="text-right">{{ $currency }} {{ number_format($financial['tithes'], 2) }}</td></tr>
                <tr><th>Offerings</th><td class="text-right">{{ $currency }} {{ number_format($financial['offerings'], 2) }}</td></tr>
                <tr><th>Pledge Payments</th><td class="text-right">{{ $currency }} {{ number_format($financial['pledges'], 2) }}</td></tr>
                <tr><th>Bereavements</th><td class="text-right">{{ $currency }} {{ number_format($financial['bereavements'], 2) }}</td></tr>
                <tr class="font-weight-bold"><th>Expenses</th><td class="text-right text-danger">{{ $currency }} {{ number_format($financial['expenses'], 2) }}</td></tr>
                <tr class="font-weight-bold"><th>Net</th><td class="text-right">{{ $currency }} {{ number_format($financial['net'], 2) }}</td></tr>
            </table>
        </div>
    </div>
</div>

<div class="tile">
    <h3 class="tile-title">Offerings by Type</h3>
    @if($report['offering_types']->isNotEmpty())
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>Type</th><th>Count</th><th class="text-right">Amount ({{ $currency }})</th></tr></thead>
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
        <p class="text-muted mb-0">No offerings in this period.</p>
    @endif
</div>
@endsection
