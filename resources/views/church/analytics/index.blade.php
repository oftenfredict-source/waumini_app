@extends('layouts.church')

@section('title', __('pages.analytics.title'))

@push('styles')
<style>
    .analytics-hero {
        background: linear-gradient(135deg, #940000 0%, #600000 100%);
        border-radius: 8px;
        color: #fff;
        padding: 1.75rem 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 8px 24px rgba(148, 0, 0, 0.18);
    }
    .analytics-hero h2 {
        color: #fff;
        margin-bottom: 0.35rem;
        font-weight: 600;
    }
    .analytics-hero .lead {
        color: rgba(255, 255, 255, 0.85);
        margin-bottom: 0;
    }
    .analytics-stat-card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        height: 100%;
    }
    .analytics-stat-card .card-body {
        padding: 1.25rem 1.35rem;
    }
    .analytics-stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        color: #fff;
    }
    .analytics-stat-value {
        font-size: 1.45rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 0.15rem;
    }
    .analytics-stat-label {
        color: #6c757d;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .analytics-chart-wrap {
        position: relative;
        min-height: 280px;
    }
    .analytics-mini-stat {
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        padding: 1rem;
        height: 100%;
    }
    .analytics-mini-stat h4 {
        font-size: 1.35rem;
        margin-bottom: 0.15rem;
        font-weight: 700;
    }
    .analytics-mini-stat p {
        margin-bottom: 0;
        color: #6c757d;
        font-size: 0.9rem;
    }
    .analytics-section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #940000;
        margin-bottom: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
</style>
@endpush

@section('content')
@php
    $currency = $analytics['currency'];
    $overview = $analytics['overview'];
    $financial = $analytics['financial'];
    $members = $analytics['members'];
    $attendance = $analytics['attendance'];
    $events = $analytics['events'];
@endphp

@include('partials.page-header', [
    'icon' => 'fa fa-line-chart',
    'title' => __('pages.analytics.heading'),
    'subtitle' => __('pages.analytics.subtitle', ['church' => $church->name]),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.analytics')],
    ],
])

@if($canViewFinance)
    <div class="mb-3 text-right">
        <a href="{{ route('church.finance.dashboard') }}" class="btn btn-primary">
            <i class="fa fa-money"></i> {{ __('pages.analytics.finance_dashboard') }}
        </a>
    </div>
@endif

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card analytics-stat-card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="analytics-stat-label">{{ __('pages.analytics.total_members') }}</div>
                    <div class="analytics-stat-value text-primary">{{ number_format($overview['total_members']) }}</div>
                    <small class="text-muted">{{ __('pages.analytics.active_label', ['count' => number_format($overview['active_members'])]) }}</small>
                </div>
                <div class="analytics-stat-icon" style="background:#940000;"><i class="fa fa-users"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card analytics-stat-card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="analytics-stat-label">{{ __('pages.analytics.income_this_month') }}</div>
                    <div class="analytics-stat-value text-success">{{ $currency }} {{ number_format($financial['monthly']['income'], 0) }}</div>
                    <small class="text-muted">{{ __('pages.analytics.net_label', ['amount' => $currency . ' ' . number_format($financial['monthly']['net'], 0)]) }}</small>
                </div>
                <div class="analytics-stat-icon" style="background:#28a745;"><i class="fa fa-money"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card analytics-stat-card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="analytics-stat-label">{{ __('pages.analytics.attendance_this_month') }}</div>
                    <div class="analytics-stat-value text-info">{{ number_format($overview['monthly_attendance']) }}</div>
                    <small class="text-muted">{{ __('pages.analytics.avg_per_service', ['count' => $attendance['average_per_service']]) }}</small>
                </div>
                <div class="analytics-stat-icon" style="background:#17a2b8;"><i class="fa fa-check-square-o"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card analytics-stat-card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="analytics-stat-label">{{ __('pages.analytics.children') }}</div>
                    <div class="analytics-stat-value text-warning">{{ number_format($overview['children']) }}</div>
                    <small class="text-muted">{{ __('pages.analytics.total_events', ['count' => number_format($overview['services_total'] + $overview['special_events_total'])]) }}</small>
                </div>
                <div class="analytics-stat-icon" style="background:#ffc107;"><i class="fa fa-child"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="analytics-section-title"><i class="fa fa-money"></i> {{ __('pages.analytics.financial_analytics') }}</div>
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-area-chart"></i> {{ __('pages.analytics.income_vs_expenses') }}</h3>
            <div class="analytics-chart-wrap">
                <canvas id="financialTrendChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-pie-chart"></i> {{ __('pages.analytics.income_mix') }}</h3>
            <div class="analytics-chart-wrap">
                <canvas id="incomeMixChart"></canvas>
            </div>
            <div class="mt-3">
                <div class="row text-center">
                    <div class="col-6 mb-2">
                        <div class="analytics-mini-stat">
                            <h4 class="text-success">{{ $currency }} {{ number_format($financial['totals']['income'], 0) }}</h4>
                            <p>{{ __('pages.analytics.all_time_income') }}</p>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="analytics-mini-stat">
                            <h4 class="text-danger">{{ $currency }} {{ number_format($financial['totals']['expenses'], 0) }}</h4>
                            <p>{{ __('pages.analytics.all_time_expenses') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="analytics-section-title"><i class="fa fa-users"></i> {{ __('pages.analytics.member_analytics') }}</div>
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-line-chart"></i> {{ __('pages.analytics.new_registrations') }}</h3>
            <div class="analytics-chart-wrap">
                <canvas id="memberRegistrationChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-pie-chart"></i> {{ __('pages.analytics.gender_distribution') }}</h3>
            <div class="analytics-chart-wrap">
                <canvas id="genderChart"></canvas>
            </div>
            <div class="mt-3">
                @foreach($members['member_types'] as $label => $count)
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>{{ $label }}</span>
                        <strong>{{ $count }}</strong>
                    </div>
                @endforeach
                @if(empty($members['member_types']))
                    <p class="text-muted mb-0">{{ __('pages.analytics.no_member_type_data') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-bar-chart"></i> {{ __('pages.analytics.age_groups') }}</h3>
            <div class="analytics-chart-wrap">
                <canvas id="ageGroupChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-id-card"></i> {{ __('pages.analytics.membership_types') }}</h3>
            <div class="row">
                @forelse($members['membership_types'] as $label => $count)
                    <div class="col-md-6 mb-3">
                        <div class="analytics-mini-stat text-center">
                            <h4>{{ $count }}</h4>
                            <p>{{ $label }}</p>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-muted mb-0">{{ __('pages.analytics.no_membership_type_data') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="analytics-section-title"><i class="fa fa-check-square-o"></i> {{ __('pages.analytics.attendance_analytics') }}</div>
<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-line-chart"></i> {{ __('pages.analytics.monthly_attendance') }}</h3>
            <div class="analytics-chart-wrap">
                <canvas id="attendanceTrendChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-trophy"></i> {{ __('pages.analytics.top_attendees') }}</h3>
            @if($attendance['top_attendees']->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('common.name') }}</th>
                                <th>{{ __('common.type') }}</th>
                                <th class="text-right">{{ __('pages.shared.count') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendance['top_attendees'] as $attendee)
                                <tr>
                                    <td>{{ $attendee['name'] }}</td>
                                    <td><span class="badge badge-{{ $attendee['type'] === 'Member' ? 'primary' : 'info' }}">{{ $attendee['type'] }}</span></td>
                                    <td class="text-right"><strong>{{ $attendee['count'] }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">{{ __('pages.analytics.no_attendance_30d') }}</p>
            @endif
            <p class="text-muted mt-3 mb-0">
                <i class="fa fa-info-circle"></i>
                {{ __('pages.analytics.attendance_summary', [
                    'total' => number_format($attendance['total']),
                    'avg' => $attendance['average_per_service'],
                    'services' => $attendance['recent_services_count'],
                ]) }}
            </p>
        </div>
    </div>
</div>

<div class="analytics-section-title"><i class="fa fa-calendar"></i> {{ __('pages.analytics.events_services') }}</div>
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.analytics.services_overview') }}</h3>
            <div class="row text-center">
                <div class="col-4">
                    <div class="analytics-mini-stat">
                        <h4>{{ $events['services']['total'] }}</h4>
                        <p>{{ __('common.total') }}</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="analytics-mini-stat">
                        <h4 class="text-success">{{ $events['services']['upcoming'] }}</h4>
                        <p>{{ __('common.upcoming') }}</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="analytics-mini-stat">
                        <h4 class="text-muted">{{ $events['services']['past'] }}</h4>
                        <p>{{ __('common.past') }}</p>
                    </div>
                </div>
            </div>
            <hr>
            <h3 class="tile-title">{{ __('pages.analytics.special_events_overview') }}</h3>
            <div class="row text-center">
                <div class="col-4">
                    <div class="analytics-mini-stat">
                        <h4>{{ $events['special_events']['total'] }}</h4>
                        <p>{{ __('common.total') }}</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="analytics-mini-stat">
                        <h4 class="text-success">{{ $events['special_events']['upcoming'] }}</h4>
                        <p>{{ __('common.upcoming') }}</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="analytics-mini-stat">
                        <h4 class="text-muted">{{ $events['special_events']['past'] }}</h4>
                        <p>{{ __('common.past') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-bar-chart"></i> {{ __('pages.analytics.services_events_chart') }}</h3>
            <div class="analytics-chart-wrap">
                <canvas id="eventsTrendChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $analyticsJs = [
        'income' => __('pages.analytics.income'),
        'expenses' => __('pages.analytics.expenses'),
        'new_members' => __('pages.analytics.new_members'),
        'male' => __('pages.analytics.male'),
        'female' => __('pages.analytics.female'),
        'members' => __('pages.shared.members'),
        'attendance_records' => __('pages.analytics.attendance_records'),
        'services' => __('pages.analytics.services'),
        'special_events' => __('pages.analytics.special_events'),
        'no_income_month' => __('pages.analytics.no_income_month'),
    ];
@endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const currency = @json($currency);
    const financialTrends = @json($financial['monthly_trends']);
    const incomeBreakdown = @json($financial['income_breakdown']);
    const memberRegistrations = @json($members['monthly_registrations']);
    const genderData = @json($members['gender']);
    const ageGroups = @json($members['age_groups']);
    const attendanceTrends = @json($attendance['monthly_trends']);
    const eventTrends = @json($events['monthly_trends']);
    const i18n = @json($analyticsJs);

    const moneyLabel = (value) => currency + ' ' + Number(value).toLocaleString();

    const financialCtx = document.getElementById('financialTrendChart');
    if (financialCtx) {
        new Chart(financialCtx, {
            type: 'bar',
            data: {
                labels: financialTrends.map(item => item.short_month),
                datasets: [
                    {
                        label: i18n.income,
                        data: financialTrends.map(item => item.income),
                        backgroundColor: 'rgba(148, 0, 0, 0.75)',
                        borderRadius: 6,
                    },
                    {
                        label: i18n.expenses,
                        data: financialTrends.map(item => item.expenses),
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
                            label: (ctx) => ctx.dataset.label + ': ' + moneyLabel(ctx.raw)
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: moneyLabel }
                    }
                }
            }
        });
    }

    const mixCtx = document.getElementById('incomeMixChart');
    if (mixCtx) {
        const amounts = incomeBreakdown.map(item => item.amount);
        const hasData = amounts.some(amount => amount > 0);

        new Chart(mixCtx, {
            type: 'doughnut',
            data: {
                labels: incomeBreakdown.map(item => item.label),
                datasets: [{
                    data: hasData ? amounts : [1],
                    backgroundColor: hasData
                        ? incomeBreakdown.map(item => item.color)
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
                            label: (ctx) => hasData
                                ? ctx.label + ': ' + moneyLabel(ctx.raw)
                                : i18n.no_income_month
                        }
                    }
                }
            }
        });
    }

    const registrationCtx = document.getElementById('memberRegistrationChart');
    if (registrationCtx) {
        new Chart(registrationCtx, {
            type: 'line',
            data: {
                labels: memberRegistrations.map(item => item.short_month),
                datasets: [{
                    label: i18n.new_members,
                    data: memberRegistrations.map(item => item.count),
                    borderColor: '#940000',
                    backgroundColor: 'rgba(148, 0, 0, 0.12)',
                    fill: true,
                    tension: 0.35,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    const genderCtx = document.getElementById('genderChart');
    if (genderCtx) {
        const labels = [i18n.male, i18n.female];
        const values = [genderData.male || 0, genderData.female || 0];
        const hasData = values.some(value => value > 0);

        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: hasData ? values : [1],
                    backgroundColor: hasData ? ['#940000', '#17a2b8'] : ['#e9ecef'],
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const ageCtx = document.getElementById('ageGroupChart');
    if (ageCtx) {
        const labels = Object.keys(ageGroups);
        const values = Object.values(ageGroups);

        new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: i18n.members,
                    data: values,
                    backgroundColor: 'rgba(148, 0, 0, 0.75)',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    const attendanceCtx = document.getElementById('attendanceTrendChart');
    if (attendanceCtx) {
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: attendanceTrends.map(item => item.short_month),
                datasets: [{
                    label: i18n.attendance_records,
                    data: attendanceTrends.map(item => item.count),
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.12)',
                    fill: true,
                    tension: 0.35,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    const eventsCtx = document.getElementById('eventsTrendChart');
    if (eventsCtx) {
        new Chart(eventsCtx, {
            type: 'bar',
            data: {
                labels: eventTrends.map(item => item.short_month),
                datasets: [
                    {
                        label: i18n.services,
                        data: eventTrends.map(item => item.services),
                        backgroundColor: 'rgba(148, 0, 0, 0.75)',
                        borderRadius: 6,
                    },
                    {
                        label: i18n.special_events,
                        data: eventTrends.map(item => item.special_events),
                        backgroundColor: 'rgba(255, 193, 7, 0.85)',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }
});
</script>
@endpush
