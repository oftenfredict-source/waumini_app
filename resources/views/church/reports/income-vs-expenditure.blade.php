@extends('layouts.church')

@section('title', 'Income vs Expenditure')

@include('church.reports.partials.styles')

@section('content')
@php $currency = $report['currency']; $financial = $report['financial']; @endphp

@include('church.reports.partials.nav')

<div class="report-hero">
    <h2><i class="fa fa-balance-scale"></i> Income vs Expenditure</h2>
    <p class="lead">{{ $start->format('M d, Y') }} – {{ $end->format('M d, Y') }}</p>
</div>

@include('church.reports.partials.date-filter')

<div class="row mb-3">
    <div class="col-md-4 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Total Income</div>
            <div class="report-stat-value text-success">{{ $currency }} {{ number_format($financial['income'], 0) }}</div>
        </div></div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Total Expenses</div>
            <div class="report-stat-value text-danger">{{ $currency }} {{ number_format($financial['expenses'], 0) }}</div>
        </div></div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Net Balance</div>
            <div class="report-stat-value {{ $financial['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $currency }} {{ number_format($financial['net'], 0) }}
            </div>
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="tile">
            <h3 class="tile-title">Income Sources</h3>
            <table class="table table-sm mb-0">
                <tr><th>Tithes</th><td class="text-right">{{ $currency }} {{ number_format($financial['tithes'], 2) }}</td></tr>
                <tr><th>Offerings</th><td class="text-right">{{ $currency }} {{ number_format($financial['offerings'], 2) }}</td></tr>
                <tr><th>Pledge Payments</th><td class="text-right">{{ $currency }} {{ number_format($financial['pledges'], 2) }}</td></tr>
                <tr><th>Bereavements</th><td class="text-right">{{ $currency }} {{ number_format($financial['bereavements'], 2) }}</td></tr>
                <tr class="font-weight-bold border-top"><th>Total Income</th><td class="text-right">{{ $currency }} {{ number_format($financial['income'], 2) }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-lg-7 mb-4">
        <div class="tile">
            <h3 class="tile-title">Monthly Comparison</h3>
            <div class="report-chart-wrap"><canvas id="incomeExpenseChart"></canvas></div>
        </div>
    </div>
</div>

<div class="tile">
    <h3 class="tile-title">Monthly Detail</h3>
    <table class="table table-sm table-hover mb-0">
        <thead><tr><th>Month</th><th class="text-right">Income</th><th class="text-right">Expenses</th><th class="text-right">Net</th></tr></thead>
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
                { label: 'Income', data: monthly.map(r => r.income), backgroundColor: 'rgba(148,0,0,0.75)', borderRadius: 6 },
                { label: 'Expenses', data: monthly.map(r => r.expenses), backgroundColor: 'rgba(220,53,69,0.65)', borderRadius: 6 },
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
