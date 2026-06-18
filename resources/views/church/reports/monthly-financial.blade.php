@extends('layouts.church')

@section('title', 'Monthly Financial Report')

@include('church.reports.partials.styles')

@section('content')
@php
    $currency = $report['currency'];
    $financial = $report['financial'];
    $period = $report['period'];
@endphp

@include('church.reports.partials.nav')

<div class="report-hero">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h2><i class="fa fa-calendar"></i> Monthly Financial Report</h2>
            <p class="lead">{{ $period['label'] }}</p>
        </div>
        <div class="col-lg-4 text-lg-right mt-3 mt-lg-0 no-print">
            <form method="GET" class="form-inline justify-content-lg-end">
                <input type="month" name="period" class="form-control mr-2" value="{{ $period['input'] }}" onchange="this.form.submit()">
            </form>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Income</div>
            <div class="report-stat-value text-success">{{ $currency }} {{ number_format($financial['income'], 0) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Expenses</div>
            <div class="report-stat-value text-danger">{{ $currency }} {{ number_format($financial['expenses'], 0) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Net</div>
            <div class="report-stat-value {{ $financial['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $currency }} {{ number_format($financial['net'], 0) }}
            </div>
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
            <h3 class="tile-title">Income Breakdown</h3>
            <table class="table table-sm mb-0">
                <tr><th>Tithes</th><td class="text-right">{{ number_format($financial['tithes'], 2) }}</td></tr>
                <tr><th>Offerings</th><td class="text-right">{{ number_format($financial['offerings'], 2) }}</td></tr>
                <tr><th>Pledge Payments</th><td class="text-right">{{ number_format($financial['pledges'], 2) }}</td></tr>
                <tr><th>Bereavements</th><td class="text-right">{{ number_format($financial['bereavements'], 2) }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title">Weekly Breakdown</h3>
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>Week</th><th class="text-right">Income</th><th class="text-right">Expenses</th></tr></thead>
                <tbody>
                    @foreach($report['weeks'] as $week)
                        <tr>
                            <td>{{ $week['label'] }}</td>
                            <td class="text-right">{{ number_format($week['income'], 2) }}</td>
                            <td class="text-right">{{ number_format($week['expenses'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
