@extends('layouts.church')

@section('title', $dashboard['is_pastor'] ? 'Pastor Dashboard' : ($dashboard['is_secretary'] ? 'Secretary Dashboard' : ($dashboard['is_treasurer'] ? 'Treasurer Dashboard' : ($dashboard['is_accountant'] ? 'Accountant Dashboard' : ($dashboard['is_administrator'] ? 'Administrator Dashboard' : 'Dashboard')))))

@push('styles')
<style>
    .dash-hero {
        background: linear-gradient(135deg, #940000 0%, #600000 100%);
        border-radius: 8px;
        color: #fff;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 8px 24px rgba(148, 0, 0, 0.18);
    }
    .dash-hero h1 {
        color: #fff;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.35rem;
    }
    .dash-hero p {
        color: rgba(255, 255, 255, 0.88);
        margin-bottom: 0;
    }
    .dash-hero .role-badge {
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #fff;
        font-size: 0.8rem;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
    }
    .dash-stat-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .dash-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }
    .dash-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: #fff;
    }
    .dash-stat-value {
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 0.1rem;
    }
    .dash-stat-label {
        color: #6c757d;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .dash-quick-actions .btn {
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .dash-section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #940000;
        margin-bottom: 1rem;
    }
    .dash-list-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .dash-list-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .dash-pending-alert {
        border-left: 4px solid #ffc107;
    }
</style>
@endpush

@section('content')
@php
    $stats = $dashboard['stats'];
    $currency = $dashboard['currency'];
@endphp

<div class="dash-hero d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1>
            <i class="fa fa-dashboard"></i>
            @if($dashboard['is_pastor'])
                Welcome, Pastor {{ $user->name }}
            @elseif($dashboard['is_secretary'])
                Welcome, Secretary {{ $user->name }}
            @elseif($dashboard['is_treasurer'])
                Welcome, Treasurer {{ $user->name }}
            @elseif($dashboard['is_accountant'])
                Welcome, Accountant {{ $user->name }}
            @elseif($dashboard['is_administrator'])
                Welcome, {{ $user->name }}
            @else
                Welcome, {{ $user->name }}
            @endif
        </h1>
        <p>{{ $church->name }} — {{ now()->format('l, F j, Y') }}</p>
    </div>
    <span class="role-badge">{{ $dashboard['role_label'] }}</span>
</div>

@if($user->hasLinkedMember() && !empty($dashboard['member_portal']))
    @include('church.dashboard.partials.my-membership')
@endif

@if(!empty($stats['pending_approvals']) && $stats['pending_approvals'] > 0)
    @can('finance.approve')
        <div class="alert alert-warning dash-pending-alert mb-3">
            <i class="fa fa-clock-o"></i>
            <strong>{{ $stats['pending_approvals'] }}</strong> finance record(s) awaiting approval
            ({{ $currency }} {{ number_format($stats['pending_approvals_amount'], 0) }}).
            <a href="{{ route('church.finance.approvals') }}" class="alert-link ml-2">Review now</a>
        </div>
    @endcan
@endif

<div class="row mb-3">
    @can('members.view')
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="tile dash-stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background:#940000;"><i class="fa fa-users"></i></div>
                    <div>
                        <div class="dash-stat-value">{{ number_format($stats['total_members']) }}</div>
                        <div class="dash-stat-label">Total Members</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="tile dash-stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background:#17a2b8;"><i class="fa fa-user"></i></div>
                    <div>
                        <div class="dash-stat-value">{{ number_format($stats['active_members']) }}</div>
                        <div class="dash-stat-label">Active Members</div>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @can('attendance.view')
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="tile dash-stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background:#28a745;"><i class="fa fa-check-square-o"></i></div>
                    <div>
                        <div class="dash-stat-value">{{ number_format($stats['monthly_attendance']) }}</div>
                        <div class="dash-stat-label">Attendance This Month</div>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @can('finance.view')
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="tile dash-stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background:#6f42c1;"><i class="fa fa-line-chart"></i></div>
                    <div>
                        <div class="dash-stat-value">{{ $currency }} {{ number_format($stats['monthly_income'] ?? 0, 0) }}</div>
                        <div class="dash-stat-label">Income This Month</div>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @can('leadership.view')
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="tile dash-stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background:#fd7e14;"><i class="fa fa-star"></i></div>
                    <div>
                        <div class="dash-stat-value">{{ number_format($stats['leaders']) }}</div>
                        <div class="dash-stat-label">Active Leaders</div>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @can('departments.view')
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="tile dash-stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background:#20c997;"><i class="fa fa-sitemap"></i></div>
                    <div>
                        <div class="dash-stat-value">{{ number_format($stats['departments']) }}</div>
                        <div class="dash-stat-label">Departments</div>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @can('finance.view')
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="tile dash-stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background:#dc3545;"><i class="fa fa-money"></i></div>
                    <div>
                        <div class="dash-stat-value">{{ $currency }} {{ number_format($stats['net_income'] ?? 0, 0) }}</div>
                        <div class="dash-stat-label">Net This Month</div>
                    </div>
                </div>
            </div>
        </div>
    @endcan
</div>

<div class="tile mb-3">
    <h3 class="dash-section-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
    <div class="dash-quick-actions">
        @can('finance.manage')
            <a href="{{ route('church.tithes.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Record Tithe
            </a>
            <a href="{{ route('church.offerings.create') }}" class="btn btn-outline-success">
                <i class="fa fa-gift"></i> Record Offering
            </a>
            <a href="{{ route('church.budget.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-briefcase"></i> Budget & Expenses
            </a>
        @endcan
        @can('bereavements.view')
            <a href="{{ route('church.bereavements.index') }}" class="btn btn-outline-dark">
                <i class="fa fa-heart"></i> Bereavements
            </a>
        @endcan
        @can('members.create')
            <a href="{{ route('church.members.create') }}" class="btn btn-primary">
                <i class="fa fa-user-plus"></i> Register Member
            </a>
        @endcan
        @can('members.view')
            <a href="{{ route('church.members.index') }}" class="btn btn-outline-primary">
                <i class="fa fa-users"></i> Members
            </a>
        @endcan
        @can('finance.view')
            <a href="{{ route('church.finance.dashboard') }}" class="btn btn-outline-success">
                <i class="fa fa-money"></i> Finance
            </a>
        @endcan
        @can('finance.approve')
            <a href="{{ route('church.finance.approvals') }}" class="btn btn-outline-warning">
                <i class="fa fa-check-circle"></i> Approvals
            </a>
        @endcan
        @can('attendance.view')
            <a href="{{ route('church.attendance.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-check-square-o"></i> Attendance
            </a>
        @endcan
        @can('special_events.view')
            <a href="{{ route('church.special-events.index') }}" class="btn btn-outline-dark">
                <i class="fa fa-calendar"></i> Events
            </a>
        @endcan
        @can('announcements.manage')
            <a href="{{ route('church.announcements.create') }}" class="btn btn-outline-info">
                <i class="fa fa-bullhorn"></i> Announce
            </a>
        @endcan
        @can('reports.view')
            <a href="{{ route('church.reports.index') }}" class="btn btn-outline-primary">
                <i class="fa fa-file-text"></i> Reports
            </a>
        @endcan
        @can('analytics.view')
            <a href="{{ route('church.analytics.index') }}" class="btn btn-outline-primary">
                <i class="fa fa-line-chart"></i> Analytics
            </a>
        @endcan
        @can('leadership.manage')
            <a href="{{ route('church.leadership.create') }}" class="btn btn-outline-warning">
                <i class="fa fa-star"></i> Assign Leader
            </a>
        @endcan
        @can('departments.manage')
            <a href="{{ route('church.departments.create') }}" class="btn btn-outline-success">
                <i class="fa fa-sitemap"></i> Add Department
            </a>
        @endcan
    </div>
</div>

<div class="row">
    @if($dashboard['upcoming_services']->isNotEmpty() || $dashboard['upcoming_events']->isNotEmpty())
        <div class="col-lg-6 mb-3">
            <div class="tile h-100">
                <h3 class="dash-section-title"><i class="fa fa-calendar"></i> Upcoming</h3>
                @foreach($dashboard['upcoming_services'] as $service)
                    <div class="dash-list-item d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $service->title }}</strong>
                            <div class="text-muted small">Service — {{ $service->service_type?->label() ?? 'Service' }}</div>
                        </div>
                        <span class="badge badge-primary">{{ $service->service_date?->format('M d') }}</span>
                    </div>
                @endforeach
                @foreach($dashboard['upcoming_events'] as $event)
                    <div class="dash-list-item d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $event->title }}</strong>
                            <div class="text-muted small">{{ $event->category?->label() ?? 'Event' }}@if($event->venue) — {{ $event->venue }}@endif</div>
                        </div>
                        <span class="badge badge-info">{{ $event->event_date?->format('M d') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($dashboard['announcements']->isNotEmpty())
        <div class="col-lg-6 mb-3">
            <div class="tile h-100">
                <h3 class="dash-section-title"><i class="fa fa-bullhorn"></i> Active Announcements</h3>
                @foreach($dashboard['announcements'] as $announcement)
                    <div class="dash-list-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <strong>
                                @if($announcement->is_pinned)<i class="fa fa-thumb-tack text-warning"></i> @endif
                                {{ $announcement->title }}
                            </strong>
                            <span class="text-muted small">{{ $announcement->created_at?->diffForHumans() }}</span>
                        </div>
                        <div class="text-muted small mt-1">{{ Str::limit(strip_tags($announcement->content), 100) }}</div>
                    </div>
                @endforeach
                @can('announcements.view')
                    <a href="{{ route('church.announcements.index') }}" class="btn btn-sm btn-link px-0 mt-2">View all announcements</a>
                @endcan
            </div>
        </div>
    @endif
</div>

@if($dashboard['is_administrator'])
    <div class="tile">
        <h3 class="tile-title">Church Information</h3>
        <table class="table table-borderless mb-0">
            <tr><th width="180">Pastor</th><td>{{ $church->pastor_name ?? '—' }}</td></tr>
            <tr><th>Email</th><td>{{ $church->email }}</td></tr>
            <tr><th>Phone</th><td>{{ $church->phone ?? '—' }}</td></tr>
            <tr><th>Location</th><td>{{ collect([$church->city, $church->country])->filter()->implode(', ') ?: '—' }}</td></tr>
            <tr><th>Status</th><td>{{ ucfirst($church->status->value) }}</td></tr>
            @if($church->activeSubscription)
                <tr><th>Package</th><td>{{ $church->activeSubscription?->package?->name ?? '—' }}</td></tr>
                <tr><th>Subscription</th><td>{{ ucfirst($church->activeSubscription->status->value) }} — {{ ucfirst($church->activeSubscription->billing_cycle->value) }}</td></tr>
            @endif
        </table>
    </div>
@elseif($dashboard['is_pastor'])
    <div class="tile">
        <h3 class="tile-title">Ministry Overview</h3>
        <div class="row">
            <div class="col-md-4">
                <p class="mb-1 text-muted small">New members this month</p>
                <h4>{{ number_format($stats['new_members_month']) }}</h4>
            </div>
            <div class="col-md-4">
                <p class="mb-1 text-muted small">Children registered</p>
                <h4>{{ number_format($stats['children']) }}</h4>
            </div>
            <div class="col-md-4">
                <p class="mb-1 text-muted small">Church contact</p>
                <h6 class="mb-0">{{ $church->email }}</h6>
                <small class="text-muted">{{ $church->phone ?? '—' }}</small>
            </div>
        </div>
    </div>
@elseif($dashboard['is_secretary'])
    <div class="tile">
        <h3 class="tile-title">Administrative Overview</h3>
        <div class="row">
            <div class="col-md-3">
                <p class="mb-1 text-muted small">New members this month</p>
                <h4>{{ number_format($stats['new_members_month']) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">Departments</p>
                <h4>{{ number_format($stats['departments']) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">Upcoming services</p>
                <h4>{{ number_format($stats['upcoming_services_count']) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">Upcoming events</p>
                <h4>{{ number_format($stats['upcoming_events_count']) }}</h4>
            </div>
        </div>
    </div>
@elseif($dashboard['is_treasurer'])
    <div class="tile mb-3">
        <h3 class="tile-title">Financial Overview — {{ $dashboard['finance']['period']['label'] ?? now()->format('F Y') }}</h3>
        <div class="row">
            <div class="col-md-3">
                <p class="mb-1 text-muted small">Monthly income</p>
                <h4 class="text-success">{{ $currency }} {{ number_format($stats['monthly_income'] ?? 0, 0) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">Monthly expenses</p>
                <h4 class="text-danger">{{ $currency }} {{ number_format($stats['monthly_expenses'] ?? 0, 0) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">Net balance</p>
                <h4>{{ $currency }} {{ number_format($stats['net_income'] ?? 0, 0) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">Active pledges</p>
                <h4>{{ number_format($stats['active_pledges'] ?? 0) }}</h4>
            </div>
        </div>
        @if(!empty($dashboard['finance']['income_breakdown']))
            <hr>
            <div class="row">
                @foreach($dashboard['finance']['income_breakdown'] as $item)
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="d-flex justify-content-between align-items-center border rounded p-2">
                            <span class="small"><i class="fa {{ $item['icon'] }}"></i> {{ $item['label'] }}</span>
                            <strong class="small">{{ $currency }} {{ number_format($item['amount'], 0) }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if(!empty($dashboard['finance']['recent_transactions']) && count($dashboard['finance']['recent_transactions']) > 0)
        <div class="tile">
            <h3 class="tile-title">Recent Transactions</h3>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dashboard['finance']['recent_transactions'] as $tx)
                            <tr>
                                <td>{{ ucfirst($tx['type'] ?? '—') }}</td>
                                <td>{{ $tx['description'] ?? ($tx['member'] ?? '—') }}</td>
                                <td>{{ isset($tx['date']) ? \Illuminate\Support\Carbon::parse($tx['date'])->format('M d, Y') : '—' }}</td>
                                <td class="text-right">{{ $currency }} {{ number_format($tx['amount'] ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@elseif($dashboard['is_accountant'])
    <div class="tile mb-3">
        <h3 class="tile-title">Accounting Overview — {{ $dashboard['finance']['period']['label'] ?? now()->format('F Y') }}</h3>
        <div class="row">
            <div class="col-md-3">
                <p class="mb-1 text-muted small">Income recorded</p>
                <h4 class="text-success">{{ $currency }} {{ number_format($stats['monthly_income'] ?? 0, 0) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">Expenses recorded</p>
                <h4 class="text-danger">{{ $currency }} {{ number_format($stats['monthly_expenses'] ?? 0, 0) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">Year-to-date expenses</p>
                <h4>{{ $currency }} {{ number_format($stats['expenses_year'] ?? 0, 0) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">Net this month</p>
                <h4>{{ $currency }} {{ number_format($stats['net_income'] ?? 0, 0) }}</h4>
            </div>
        </div>
        @if(!empty($dashboard['finance']['income_breakdown']))
            <hr>
            <div class="row">
                @foreach($dashboard['finance']['income_breakdown'] as $item)
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="d-flex justify-content-between align-items-center border rounded p-2">
                            <span class="small"><i class="fa {{ $item['icon'] }}"></i> {{ $item['label'] }}</span>
                            <strong class="small">{{ $currency }} {{ number_format($item['amount'], 0) }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if(!empty($dashboard['finance']['recent_transactions']) && count($dashboard['finance']['recent_transactions']) > 0)
        <div class="tile">
            <h3 class="tile-title">Recent Entries</h3>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dashboard['finance']['recent_transactions'] as $tx)
                            <tr>
                                <td>{{ ucfirst($tx['type'] ?? '—') }}</td>
                                <td>{{ $tx['description'] ?? ($tx['member'] ?? '—') }}</td>
                                <td>{{ isset($tx['date']) ? \Illuminate\Support\Carbon::parse($tx['date'])->format('M d, Y') : '—' }}</td>
                                <td class="text-right">{{ $currency }} {{ number_format($tx['amount'] ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endif
@endsection
