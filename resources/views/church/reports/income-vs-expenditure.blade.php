@extends('layouts.church')

@section('title', __('reports.income_vs_expenditure'))

@include('church.reports.partials.styles')

@section('content')
@php $currency = $report['currency']; $financial = $report['financial']; @endphp

@include('church.reports.partials.nav')

<div class="report-hero">
    <h2><i class="fa fa-balance-scale"></i> {{ __('reports.income_vs_expenditure') }}</h2>
    <p class="lead">{{ $start->format('M d, Y') }} – {{ $end->format('M d, Y') }}</p>
</div>

@include('church.reports.partials.date-filter')

<div class="row mb-3">
    <div class="col-md-4 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">{{ __('reports.total_income') }}</div>
            <div class="report-stat-value text-success">{{ $currency }} {{ number_format($financial['income'], 0) }}</div>
        </div></div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">{{ __('reports.total_expenses') }}</div>
            <div class="report-stat-value text-danger">{{ $currency }} {{ number_format($financial['expenses'], 0) }}</div>
        </div></div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">{{ __('reports.net_balance') }}</div>
            <div class="report-stat-value {{ $financial['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $currency }} {{ number_format($financial['net'], 0) }}
            </div>
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('reports.income_sources') }}</h3>
            <table class="table table-sm mb-0">
                <tr><th>{{ __('reports.tithes') }}</th><td class="text-right">{{ $currency }} {{ number_format($financial['tithes'], 2) }}</td></tr>
                <tr><th>{{ __('reports.offerings') }}</th><td class="text-right">{{ $currency }} {{ number_format($financial['offerings'], 2) }}</td></tr>
                <tr><th>{{ __('reports.pledge_payments') }}</th><td class="text-right">{{ $currency }} {{ number_format($financial['pledges'], 2) }}</td></tr>
                <tr><th>{{ __('reports.bereavements') }}</th><td class="text-right">{{ $currency }} {{ number_format($financial['bereavements'], 2) }}</td></tr>
                <tr class="font-weight-bold border-top"><th>{{ __('reports.total_income') }}</th><td class="text-right">{{ $currency }} {{ number_format($financial['income'], 2) }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-lg-7 mb-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('reports.monthly_comparison') }}</h3>
            <div class="report-chart-wrap"><canvas id="incomeExpenseChart"></canvas></div>
        </div>
    </div>
</div>

<div class="tile">
    <h3 class="tile-title">{{ __('reports.monthly_detail') }}</h3>
    <table class="table table-sm table-hover mb-0">
        <thead><tr><th>{{ __('reports.month') }}</th><th class="text-right">{{ __('reports.income') }}</th><th class="text-right">{{ __('reports.expenses') }}</th><th class="text-right">{{ __('reports.net') }}</th></tr></thead>
        <tbody>
            @foreach($report['monthly'] as $row)
                <tr>
                    <td>{{ $row['month'] }}</td>
                    <td class="text-right">{{ number_format($row['income'], 2) }}</td>
                    <td class="text-right">{{ number_format($row['expenses'], 2) }}</td>
                    <td class="text-right font-weight-bold {{ $row['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($row['net'], 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const currency = @json($currency);
    const monthly = @json($report['monthly']);
    const ctx = document.getElementById('incomeExpenseChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthly.map(r => r.month),
            datasets: [
                { label: @json(__('reports.income')), data: monthly.map(r => r.income), backgroundColor: 'rgba(148,0,0,0.75)', borderRadius: 6 },
                { label: @json(__('reports.expenses')), data: monthly.map(r => r.expenses), backgroundColor: 'rgba(220,53,69,0.65)', borderRadius: 6 },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => currency + ' ' + Number(v).toLocaleString() } } }
        }
    });
});
</script>
@endpush
