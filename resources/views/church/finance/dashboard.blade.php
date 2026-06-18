@extends('layouts.church')

@section('title', 'Finance Dashboard')

@push('styles')
<style>
    .finance-hero {
        background: linear-gradient(135deg, #940000 0%, #600000 100%);
        border-radius: 8px;
        color: #fff;
        padding: 1.75rem 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 8px 24px rgba(148, 0, 0, 0.18);
    }
    .finance-hero h2 {
        color: #fff;
        margin-bottom: 0.35rem;
        font-weight: 600;
    }
    .finance-hero .lead {
        color: rgba(255, 255, 255, 0.85);
        margin-bottom: 0;
    }
    .finance-stat-card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        height: 100%;
    }
    .finance-stat-card .card-body {
        padding: 1.25rem 1.35rem;
    }
    .finance-stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        color: #fff;
    }
    .finance-stat-value {
        font-size: 1.45rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 0.15rem;
    }
    .finance-stat-label {
        color: #6c757d;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .finance-change-up { color: #28a745; font-weight: 600; }
    .finance-change-down { color: #dc3545; font-weight: 600; }
    .finance-change-neutral { color: #6c757d; }
    .finance-breakdown-item + .finance-breakdown-item {
        border-top: 1px solid #f0f0f0;
        margin-top: 1rem;
        padding-top: 1rem;
    }
    .finance-breakdown-bar {
        height: 8px;
        border-radius: 999px;
        background: #f1f3f5;
        overflow: hidden;
    }
    .finance-breakdown-bar > span {
        display: block;
        height: 100%;
        border-radius: 999px;
    }
    .finance-transaction {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.85rem 0;
        border-bottom: 1px solid #f3f3f3;
    }
    .finance-transaction:last-child { border-bottom: none; }
    .finance-transaction-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        flex-shrink: 0;
    }
    .finance-quick-link {
        display: block;
        text-align: center;
        padding: 1.25rem 0.75rem;
        border: 1px solid #eee;
        border-radius: 10px;
        color: #333;
        transition: all 0.2s ease;
        height: 100%;
    }
    .finance-quick-link:hover {
        text-decoration: none;
        border-color: #940000;
        box-shadow: 0 6px 18px rgba(148, 0, 0, 0.08);
        transform: translateY(-2px);
        color: #940000;
    }
    .finance-quick-link i {
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
        color: #940000;
    }
    .finance-chart-wrap {
        position: relative;
        min-height: 260px;
    }
    .bereavement-progress {
        height: 8px;
        border-radius: 999px;
        background: #f1f3f5;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
@php
    $summary = $dashboard['summary'];
    $period = $dashboard['period'];
    $breakdown = $dashboard['income_breakdown'];
    $change = $summary['income_change_percent'];
@endphp

<div class="finance-hero">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h2><i class="fa fa-line-chart"></i> Finance Dashboard</h2>
            <p class="lead">Overview of church income, collections, and financial health for {{ $period['label'] }}.</p>
        </div>
        <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
            <form method="GET" class="form-inline justify-content-lg-end">
                <input type="month" name="period" class="form-control mr-2 mb-2"
                    value="{{ $period['input'] }}" onchange="this.form.submit()">
            </form>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card finance-stat-card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="finance-stat-label">Income ({{ $period['label'] }})</div>
                    <div class="finance-stat-value text-primary">TZS {{ number_format($summary['total_income'], 0) }}</div>
                    @if($change !== null)
                        <small class="{{ $change >= 0 ? 'finance-change-up' : 'finance-change-down' }}">
                            <i class="fa fa-{{ $change >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                            {{ abs($change) }}% vs last month
                        </small>
                    @else
                        <small class="finance-change-neutral">No prior month data</small>
                    @endif
                </div>
                <div class="finance-stat-icon" style="background:#940000;"><i class="fa fa-arrow-up"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card finance-stat-card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="finance-stat-label">Net (This Month)</div>
                    <div class="finance-stat-value text-success">TZS {{ number_format($summary['net_balance'], 0) }}</div>
                    <small class="text-muted">Income minus expenses</small>
                </div>
                <div class="finance-stat-icon" style="background:#28a745;"><i class="fa fa-balance-scale"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card finance-stat-card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="finance-stat-label">All-Time Balance</div>
                    <div class="finance-stat-value">TZS {{ number_format($summary['all_time_balance'], 0) }}</div>
                    <small class="text-muted">Recorded church finances</small>
                </div>
                <div class="finance-stat-icon" style="background:#17a2b8;"><i class="fa fa-university"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card finance-stat-card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="finance-stat-label">Bereavement Income</div>
                    <div class="finance-stat-value" style="color:#6f42c1;">TZS {{ number_format($summary['bereavement_month'], 0) }}</div>
                    <small class="text-muted">{{ $dashboard['quick_stats']['contributors_this_month'] }} contributors</small>
                </div>
                <div class="finance-stat-icon" style="background:#6f42c1;"><i class="fa fa-heart"></i></div>
            </div>
        </div>
    </div>
</div>

@if($canApprove)
<div class="row mb-3">
    <div class="col-md-12">
        <div class="alert alert-light border d-flex justify-content-between align-items-center mb-0">
            <div>
                <i class="fa fa-check-circle text-warning"></i>
                <strong>Pending Approvals:</strong>
                {{ $summary['pending_approvals_count'] }} items
                @if($summary['pending_approvals_amount'] > 0)
                    (TZS {{ number_format($summary['pending_approvals_amount'], 0) }})
                @endif
            </div>
            <a href="{{ route('church.finance.approvals') }}" class="btn btn-sm btn-outline-primary">
                Open Approval Dashboard
            </a>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-area-chart"></i> Income vs Expenses (6 Months)</h3>
            <div class="finance-chart-wrap">
                <canvas id="incomeExpenseChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-pie-chart"></i> Income Mix</h3>
            <div class="finance-chart-wrap">
                <canvas id="incomeMixChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-bar-chart"></i> Income Breakdown ({{ $period['label'] }})</h3>
            @foreach($breakdown as $item)
                <div class="finance-breakdown-item">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>
                            <i class="fa {{ $item['icon'] }}" style="color:{{ $item['color'] }};"></i>
                            <strong>{{ $item['label'] }}</strong>
                        </div>
                        <span>TZS {{ number_format($item['amount'], 0) }}</span>
                    </div>
                    <div class="finance-breakdown-bar">
                        <span style="width:{{ $item['percent'] }}%; background:{{ $item['color'] }};"></span>
                    </div>
                    <small class="text-muted">{{ $item['percent'] }}% of total income</small>
                </div>
            @endforeach
            @if($summary['total_income'] == 0)
                <p class="text-muted mt-3 mb-0">No income recorded for this period yet. Bereavement, tithe, offerings, and pledge payments will appear here as transactions are recorded.</p>
            @endif
        </div>
    </div>

    <div class="col-lg-7 mb-4">
        <div class="tile">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="tile-title mb-0"><i class="fa fa-history"></i> Recent Transactions</h3>
                <a href="{{ route('church.bereavements.index') }}" class="btn btn-sm btn-outline-primary">Bereavements</a>
            </div>
            @forelse($dashboard['recent_transactions'] as $transaction)
                <div class="finance-transaction">
                    <div class="finance-transaction-icon" style="background:#6f42c1;">
                        <i class="fa {{ $transaction['icon'] }}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="font-weight-bold">{{ $transaction['member'] }}</div>
                        <div class="small text-muted">{{ $transaction['description'] }}</div>
                        <div class="small text-muted">{{ $transaction['date']?->format('M d, Y') ?? '—' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-weight-bold text-success">TZS {{ number_format($transaction['amount'], 0) }}</div>
                        <span class="badge {{ $transaction['badge_class'] }}">{{ $transaction['label'] }}</span>
                        @if($transaction['route'])
                            <div><a href="{{ $transaction['route'] }}" class="small">View</a></div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    <i class="fa fa-inbox fa-2x mb-2"></i>
                    <p>No recent transactions yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-heart"></i> Open Bereavement Collections</h3>
            @forelse($dashboard['open_bereavements'] as $event)
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $event->deceased_name }}</strong>
                            <div class="small text-muted">
                                Ends {{ $event->contribution_end_date->format('M d, Y') }}
                                · {{ $event->daysRemaining() }} days left
                            </div>
                        </div>
                        <a href="{{ route('church.bereavements.show', $event) }}" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                    <div class="d-flex justify-content-between small mt-2 mb-1">
                        <span>TZS {{ number_format($event->total_raised ?? 0, 0) }} raised</span>
                        <span>{{ $event->contributors_count ?? 0 }} contributors</span>
                    </div>
                    <div class="bereavement-progress">
                        @php
                            $target = max(($event->total_raised ?? 0) * 1.2, 1);
                            $pct = min(100, round((($event->total_raised ?? 0) / $target) * 100));
                        @endphp
                        <div class="bg-primary" style="height:100%; width:{{ $pct }}%; border-radius:999px;"></div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-3">
                    <i class="fa fa-heart-o fa-2x mb-2"></i>
                    <p>No open bereavement collections.</p>
                    @can('create', \App\Models\BereavementEvent::class)
                        <a href="{{ route('church.bereavements.create') }}" class="btn btn-sm btn-primary">Create Bereavement</a>
                    @endcan
                </div>
            @endforelse
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
            <div class="row">
                <div class="col-md-4 col-6 mb-3">
                    <a href="{{ route('church.tithes.index') }}" class="finance-quick-link">
                        <i class="fa fa-money d-block"></i>
                        <strong>Tithes</strong>
                    </a>
                </div>
                <div class="col-md-4 col-6 mb-3">
                    <a href="{{ route('church.offerings.index') }}" class="finance-quick-link">
                        <i class="fa fa-gift d-block"></i>
                        <strong>Offerings</strong>
                    </a>
                </div>
                <div class="col-md-4 col-6 mb-3">
                    <a href="{{ route('church.pledges.index') }}" class="finance-quick-link">
                        <i class="fa fa-handshake-o d-block"></i>
                        <strong>Pledges</strong>
                    </a>
                </div>
                <div class="col-md-4 col-6 mb-3">
                    <a href="{{ route('church.budget.index') }}" class="finance-quick-link">
                        <i class="fa fa-file-text-o d-block"></i>
                        <strong>Budget</strong>
                    </a>
                </div>
                <div class="col-md-4 col-6 mb-3">
                    <a href="{{ route('church.bereavements.index') }}" class="finance-quick-link">
                        <i class="fa fa-heart d-block"></i>
                        <strong>Bereavements</strong>
                    </a>
                </div>
                <div class="col-md-4 col-6 mb-3">
                    <a href="{{ route('church.reports.index') }}" class="finance-quick-link">
                        <i class="fa fa-bar-chart d-block"></i>
                        <strong>Reports</strong>
                    </a>
                </div>
            </div>
            @if($canManage)
                <div class="alert alert-info mb-0 mt-2">
                    <i class="fa fa-info-circle"></i>
                    Budgets & expenses, tithes, offerings, pledge payments, and bereavements are now reflected on this dashboard.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const brandColor = '#940000';
    const trendData = @json($dashboard['income_trend']);
    const breakdownData = @json($breakdown);

    const incomeExpenseCtx = document.getElementById('incomeExpenseChart');
    if (incomeExpenseCtx) {
        new Chart(incomeExpenseCtx, {
            type: 'bar',
            data: {
                labels: trendData.map(item => item.short_month),
                datasets: [
                    {
                        label: 'Income',
                        data: trendData.map(item => item.income),
                        backgroundColor: 'rgba(148, 0, 0, 0.75)',
                        borderRadius: 6,
                    },
                    {
                        label: 'Expenses',
                        data: trendData.map(item => item.expenses),
                        backgroundColor: 'rgba(220, 53, 69, 0.65)',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.dataset.label + ': TZS ' + Number(ctx.raw).toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => 'TZS ' + Number(value).toLocaleString()
                        }
                    }
                }
            }
        });
    }

    const mixCtx = document.getElementById('incomeMixChart');
    if (mixCtx) {
        const amounts = breakdownData.map(item => item.amount);
        const hasData = amounts.some(amount => amount > 0);

        new Chart(mixCtx, {
            type: 'doughnut',
            data: {
                labels: breakdownData.map(item => item.label),
                datasets: [{
                    data: hasData ? amounts : [1],
                    backgroundColor: hasData
                        ? breakdownData.map(item => item.color)
                        : ['#e9ecef'],
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                if (!hasData) return 'No income recorded';
                                return ctx.label + ': TZS ' + Number(ctx.raw).toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
