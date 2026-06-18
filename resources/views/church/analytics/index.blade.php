@extends('layouts.church')

@section('title', 'Analytics')

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

<div class="analytics-hero">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h2><i class="fa fa-line-chart"></i> Church Analytics</h2>
            <p class="lead">Insights across members, finances, attendance, and events for {{ $church->name }}.</p>
        </div>
        <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
            @if($canViewFinance)
                <a href="{{ route('church.finance.dashboard') }}" class="btn btn-light">
                    <i class="fa fa-money"></i> Finance Dashboard
                </a>
            @endif
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card analytics-stat-card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="analytics-stat-label">Total Members</div>
                    <div class="analytics-stat-value text-primary">{{ number_format($overview['total_members']) }}</div>
                    <small class="text-muted">{{ number_format($overview['active_members']) }} active</small>
                </div>
                <div class="analytics-stat-icon" style="background:#940000;"><i class="fa fa-users"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card analytics-stat-card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="analytics-stat-label">Income (This Month)</div>
                    <div class="analytics-stat-value text-success">{{ $currency }} {{ number_format($financial['monthly']['income'], 0) }}</div>
                    <small class="text-muted">Net {{ $currency }} {{ number_format($financial['monthly']['net'], 0) }}</small>
                </div>
                <div class="analytics-stat-icon" style="background:#28a745;"><i class="fa fa-money"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card analytics-stat-card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="analytics-stat-label">Attendance (This Month)</div>
                    <div class="analytics-stat-value text-info">{{ number_format($overview['monthly_attendance']) }}</div>
                    <small class="text-muted">Avg {{ $attendance['average_per_service'] }} per service</small>
                </div>
                <div class="analytics-stat-icon" style="background:#17a2b8;"><i class="fa fa-check-square-o"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card analytics-stat-card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="analytics-stat-label">Children</div>
                    <div class="analytics-stat-value text-warning">{{ number_format($overview['children']) }}</div>
                    <small class="text-muted">{{ number_format($overview['services_total'] + $overview['special_events_total']) }} total events</small>
                </div>
                <div class="analytics-stat-icon" style="background:#ffc107;"><i class="fa fa-child"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="analytics-section-title"><i class="fa fa-money"></i> Financial Analytics</div>
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-area-chart"></i> Income vs Expenses (12 Months)</h3>
            <div class="analytics-chart-wrap">
                <canvas id="financialTrendChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-pie-chart"></i> Income Mix (This Month)</h3>
            <div class="analytics-chart-wrap">
                <canvas id="incomeMixChart"></canvas>
            </div>
            <div class="mt-3">
                <div class="row text-center">
                    <div class="col-6 mb-2">
                        <div class="analytics-mini-stat">
                            <h4 class="text-success">{{ $currency }} {{ number_format($financial['totals']['income'], 0) }}</h4>
                            <p>All-time income</p>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="analytics-mini-stat">
                            <h4 class="text-danger">{{ $currency }} {{ number_format($financial['totals']['expenses'], 0) }}</h4>
                            <p>All-time expenses</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="analytics-section-title"><i class="fa fa-users"></i> Member Analytics</div>
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-line-chart"></i> New Member Registrations (12 Months)</h3>
            <div class="analytics-chart-wrap">
                <canvas id="memberRegistrationChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-pie-chart"></i> Gender Distribution</h3>
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
                    <p class="text-muted mb-0">No member type data yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-bar-chart"></i> Age Groups</h3>
            <div class="analytics-chart-wrap">
                <canvas id="ageGroupChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-id-card"></i> Membership Types</h3>
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
                        <p class="text-muted mb-0">No membership type data yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="analytics-section-title"><i class="fa fa-check-square-o"></i> Attendance Analytics</div>
<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-line-chart"></i> Monthly Attendance (12 Months)</h3>
            <div class="analytics-chart-wrap">
                <canvas id="attendanceTrendChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-trophy"></i> Top Attendees (Last 30 Days)</h3>
            @if($attendance['top_attendees']->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th class="text-right">Count</th>
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
                <p class="text-muted mb-0">No attendance records in the last 30 days.</p>
            @endif
            <p class="text-muted mt-3 mb-0">
                <i class="fa fa-info-circle"></i>
                {{ number_format($attendance['total']) }} total attendance records.
                Average of {{ $attendance['average_per_service'] }} attendees across {{ $attendance['recent_services_count'] }} recent services.
            </p>
        </div>
    </div>
</div>

<div class="analytics-section-title"><i class="fa fa-calendar"></i> Events & Services</div>
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="tile">
            <h3 class="tile-title">Services Overview</h3>
            <div class="row text-center">
                <div class="col-4">
                    <div class="analytics-mini-stat">
                        <h4>{{ $events['services']['total'] }}</h4>
                        <p>Total</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="analytics-mini-stat">
                        <h4 class="text-success">{{ $events['services']['upcoming'] }}</h4>
                        <p>Upcoming</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="analytics-mini-stat">
                        <h4 class="text-muted">{{ $events['services']['past'] }}</h4>
                        <p>Past</p>
                    </div>
                </div>
            </div>
            <hr>
            <h3 class="tile-title">Special Events Overview</h3>
            <div class="row text-center">
                <div class="col-4">
                    <div class="analytics-mini-stat">
                        <h4>{{ $events['special_events']['total'] }}</h4>
                        <p>Total</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="analytics-mini-stat">
                        <h4 class="text-success">{{ $events['special_events']['upcoming'] }}</h4>
                        <p>Upcoming</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="analytics-mini-stat">
                        <h4 class="text-muted">{{ $events['special_events']['past'] }}</h4>
                        <p>Past</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8 mb-4">
        <div class="tile">
            <h3 class="tile-title"><i class="fa fa-bar-chart"></i> Services & Events (12 Months)</h3>
            <div class="analytics-chart-wrap">
                <canvas id="eventsTrendChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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

    const moneyLabel = (value) => currency + ' ' + Number(value).toLocaleString();

    const financialCtx = document.getElementById('financialTrendChart');
    if (financialCtx) {
        new Chart(financialCtx, {
            type: 'bar',
            data: {
                labels: financialTrends.map(item => item.short_month),
                datasets: [
                    {
                        label: 'Income',
                        data: financialTrends.map(item => item.income),
                        backgroundColor: 'rgba(148, 0, 0, 0.75)',
                        borderRadius: 6,
                    },
                    {
                        label: 'Expenses',
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
                                : 'No income recorded this month'
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
                    label: 'New Members',
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
        const labels = ['Male', 'Female'];
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
                    label: 'Members',
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
                    label: 'Attendance Records',
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
                        label: 'Services',
                        data: eventTrends.map(item => item.services),
                        backgroundColor: 'rgba(148, 0, 0, 0.75)',
                        borderRadius: 6,
                    },
                    {
                        label: 'Special Events',
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
