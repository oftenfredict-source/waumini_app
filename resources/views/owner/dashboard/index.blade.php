@extends('layouts.owner')

@section('title', __('owner.dash.title'))

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">{{ __('owner.dash.heading') }}</h1>
        <p class="text-muted mb-0">{{ __('owner.dash.subtitle') }}</p>
    </div>
    <a href="{{ route('owner.churches.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> {{ __('owner.dash.add_church') }}
    </a>
</div>

<div class="row g-3 g-lg-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.dash.total_churches') }}</p>
                        <div class="h3 mb-0">{{ number_format($overview['total_churches']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(22,163,74,.12);color:#16a34a;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.dash.active_churches') }}</p>
                        <div class="h3 mb-0">{{ number_format($overview['active_churches']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(217,119,6,.12);color:#d97706;">
                        <i class="bi bi-pause-circle"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.dash.suspended') }}</p>
                        <div class="h3 mb-0">{{ number_format($overview['suspended_churches']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(220,38,38,.12);color:#dc2626;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.dash.expired') }}</p>
                        <div class="h3 mb-0">{{ number_format($overview['expired_churches']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 g-lg-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(8,145,178,.12);color:#0891b2;">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.dash.new_signups_30d') }}</p>
                        <div class="h3 mb-0">{{ number_format($overview['new_signups_30d']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(124,58,237,.12);color:#7c3aed;">
                        <i class="bi bi-calendar3"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.dash.new_signups_7d') }}</p>
                        <div class="h3 mb-0">{{ number_format($overview['new_signups_7d']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(22,163,74,.12);color:#16a34a;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.dash.mrr') }}</p>
                        <div class="h3 mb-0">{{ $overview['currency'] }} {{ number_format($overview['mrr'], 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(234,88,12,.12);color:#ea580c;">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.dash.arr') }}</p>
                        <div class="h3 mb-0">{{ $overview['currency'] }} {{ number_format($overview['arr'], 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 g-lg-4 mb-4">
    <div class="col-lg-6">
        <div class="card owner-panel-card">
            <div class="card-header">{{ __('owner.dash.signups_chart') }}</div>
            <div class="card-body">
                <div class="owner-chart-box">
                    <canvas id="signupsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card owner-panel-card">
            <div class="card-header">{{ __('owner.dash.by_package') }}</div>
            <div class="card-body">
                <div class="owner-chart-box">
                    <canvas id="packageChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card owner-panel-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ __('owner.dash.recent_churches') }}</span>
        <a href="{{ route('owner.churches.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('common.view') }}</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>{{ __('owner.church_label') }}</th>
                        <th>{{ __('owner.email') }}</th>
                        <th>{{ __('owner.package') }}</th>
                        <th>{{ __('owner.status') }}</th>
                        <th>{{ __('owner.dash.joined') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentChurches as $church)
                        <tr>
                            <td class="fw-semibold">{{ $church->name }}</td>
                            <td>{{ $church->email }}</td>
                            <td>{{ $church->activeSubscription?->package?->name ?? '—' }}</td>
                            <td>@include('owner.components.status-badge', ['status' => $church->status])</td>
                            <td>{{ $church->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('owner.churches.show', $church) }}" class="btn btn-sm btn-outline-primary">{{ __('common.view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">{{ __('owner.dash.no_churches') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    var signupsLabels = @json($signupsChart['labels']);
    var signupsValues = @json($signupsChart['data']);
    var packageLabels = @json($churchesByPackage->keys()->values());
    var packageData = @json($churchesByPackage->values());
    var colors = ['#940000', '#0891b2', '#16a34a', '#d97706', '#7c3aed', '#ea580c'];

    var signupsEl = document.getElementById('signupsChart');
    if (signupsEl) {
        new Chart(signupsEl, {
            type: 'line',
            data: {
                labels: signupsLabels,
                datasets: [{
                    label: @json(__('owner.dash.chart_new_churches')),
                    data: signupsValues,
                    borderColor: '#940000',
                    backgroundColor: 'rgba(148, 0, 0, 0.12)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    var packageEl = document.getElementById('packageChart');
    if (packageEl && packageLabels.length) {
        new Chart(packageEl, {
            type: 'doughnut',
            data: {
                labels: packageLabels,
                datasets: [{
                    data: packageData,
                    backgroundColor: packageLabels.map(function (_, i) { return colors[i % colors.length]; }),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
})();
</script>
@endpush
