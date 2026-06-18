@extends('layouts.church')

@section('title', 'Offering Breakdown')

@include('church.reports.partials.styles')

@section('content')
@php $currency = $report['currency']; @endphp

@include('church.reports.partials.nav')

<div class="report-hero">
    <h2><i class="fa fa-gift"></i> Offering Breakdown</h2>
    <p class="lead">{{ $start->format('M d, Y') }} – {{ $end->format('M d, Y') }}</p>
</div>

@include('church.reports.partials.date-filter')

<div class="row mb-3">
    <div class="col-md-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Total Offerings</div>
            <div class="report-stat-value text-success">{{ $currency }} {{ number_format($report['total'], 2) }}</div>
        </div></div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card report-stat-card"><div class="card-body">
            <div class="report-stat-label">Transactions</div>
            <div class="report-stat-value">{{ number_format($report['transaction_count']) }}</div>
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="tile">
            <h3 class="tile-title">By Offering Type</h3>
            @if($report['by_type']->isNotEmpty())
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Type</th><th>Count</th><th class="text-right">Amount</th></tr></thead>
                    <tbody>
                        @foreach($report['by_type'] as $row)
                            <tr>
                                <td>{{ $row['type'] }}</td>
                                <td>{{ $row['count'] }}</td>
                                <td class="text-right">{{ number_format($row['total'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted mb-0">No offerings in this period.</p>
            @endif
        </div>
    </div>
    <div class="col-lg-7 mb-4">
        <div class="tile">
            <h3 class="tile-title">Distribution Chart</h3>
            <div class="report-chart-wrap"><canvas id="offeringChart"></canvas></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const data = @json($report['by_type']);
    const ctx = document.getElementById('offeringChart');
    if (!ctx || !data.length) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(r => r.type),
            datasets: [{ data: data.map(r => r.total), backgroundColor: ['#940000','#28a745','#ffc107','#17a2b8','#6f42c1','#fd7e14'] }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });
});
</script>
@endpush
