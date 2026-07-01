@extends('layouts.church')

@php
    $dashboardTitle = match (true) {
        $dashboard['is_pastor'] => __('pages.dashboard.title_pastor'),
        $dashboard['is_secretary'] => __('pages.dashboard.title_secretary'),
        $dashboard['is_treasurer'] => __('pages.dashboard.title_treasurer'),
        $dashboard['is_accountant'] => __('pages.dashboard.title_accountant'),
        $dashboard['is_administrator'] => __('pages.dashboard.title_administrator'),
        default => __('pages.dashboard.title'),
    };
@endphp

@section('title', $dashboardTitle)

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
                {{ __('pages.dashboard.welcome_pastor', ['name' => $user->name]) }}
            @elseif($dashboard['is_secretary'])
                {{ __('pages.dashboard.welcome_secretary', ['name' => $user->name]) }}
            @elseif($dashboard['is_treasurer'])
                {{ __('pages.dashboard.welcome_treasurer', ['name' => $user->name]) }}
            @elseif($dashboard['is_accountant'])
                {{ __('pages.dashboard.welcome_accountant', ['name' => $user->name]) }}
            @else
                {{ __('pages.dashboard.welcome', ['name' => $user->name]) }}
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
            {{ __('pages.dashboard.pending_approvals', [
                'count' => $stats['pending_approvals'],
                'amount' => $currency . ' ' . number_format($stats['pending_approvals_amount'], 0),
            ]) }}
            <a href="{{ route('church.finance.approvals') }}" class="alert-link ml-2">{{ __('pages.dashboard.review_now') }}</a>
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
                        <div class="dash-stat-label">{{ __('pages.dashboard.total_members') }}</div>
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
                        <div class="dash-stat-label">{{ __('pages.dashboard.active_members') }}</div>
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
                        <div class="dash-stat-label">{{ __('pages.dashboard.attendance_this_month') }}</div>
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
                        <div class="dash-stat-label">{{ __('pages.dashboard.income_this_month') }}</div>
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
                        <div class="dash-stat-label">{{ __('pages.dashboard.active_leaders') }}</div>
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
                        <div class="dash-stat-label">{{ __('pages.dashboard.departments') }}</div>
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
                        <div class="dash-stat-label">{{ __('pages.dashboard.net_this_month') }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endcan
</div>

<div class="tile mb-3">
    <h3 class="dash-section-title"><i class="fa fa-bolt"></i> {{ __('pages.dashboard.quick_actions') }}</h3>
    <div class="dash-quick-actions">
        @can('finance.manage')
            <a href="{{ route('church.tithes.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> {{ __('pages.dashboard.record_tithe') }}
            </a>
            <a href="{{ route('church.offerings.create') }}" class="btn btn-outline-success">
                <i class="fa fa-gift"></i> {{ __('pages.dashboard.record_offering') }}
            </a>
            <a href="{{ route('church.budget.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-briefcase"></i> {{ __('menu.budget_expenses') }}
            </a>
        @endcan
        @can('bereavements.view')
            <a href="{{ route('church.bereavements.index') }}" class="btn btn-outline-dark">
                <i class="fa fa-heart"></i> {{ __('menu.bereavements') }}
            </a>
        @endcan
        @can('members.create')
            <a href="{{ route('church.members.create') }}" class="btn btn-primary">
                <i class="fa fa-user-plus"></i> {{ __('menu.register_member') }}
            </a>
        @endcan
        @can('members.view')
            <a href="{{ route('church.members.index') }}" class="btn btn-outline-primary">
                <i class="fa fa-users"></i> {{ __('menu.members') }}
            </a>
        @endcan
        @can('finance.view')
            <a href="{{ route('church.finance.dashboard') }}" class="btn btn-outline-success">
                <i class="fa fa-money"></i> {{ __('pages.dashboard.finance') }}
            </a>
        @endcan
        @can('finance.approve')
            <a href="{{ route('church.finance.approvals') }}" class="btn btn-outline-warning">
                <i class="fa fa-check-circle"></i> {{ __('pages.shared.approvals') }}
            </a>
        @endcan
        @can('attendance.view')
            <a href="{{ route('church.attendance.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-check-square-o"></i> {{ __('menu.attendance') }}
            </a>
        @endcan
        @can('special_events.view')
            <a href="{{ route('church.special-events.index') }}" class="btn btn-outline-dark">
                <i class="fa fa-calendar"></i> {{ __('pages.dashboard.events') }}
            </a>
        @endcan
        @can('announcements.manage')
            <a href="{{ route('church.announcements.create') }}" class="btn btn-outline-info">
                <i class="fa fa-bullhorn"></i> {{ __('pages.dashboard.announce') }}
            </a>
        @endcan
        @can('reports.view')
            <a href="{{ route('church.reports.index') }}" class="btn btn-outline-primary">
                <i class="fa fa-file-text"></i> {{ __('pages.dashboard.reports') }}
            </a>
        @endcan
        @can('analytics.view')
            <a href="{{ route('church.analytics.index') }}" class="btn btn-outline-primary">
                <i class="fa fa-line-chart"></i> {{ __('menu.analytics') }}
            </a>
        @endcan
        @can('leadership.manage')
            <a href="{{ route('church.leadership.create') }}" class="btn btn-outline-warning">
                <i class="fa fa-star"></i> {{ __('pages.dashboard.assign_leader') }}
            </a>
        @endcan
        @can('departments.manage')
            <a href="{{ route('church.departments.create') }}" class="btn btn-outline-success">
                <i class="fa fa-sitemap"></i> {{ __('menu.add_department') }}
            </a>
        @endcan
    </div>
</div>

<div class="row">
    @if($dashboard['upcoming_services']->isNotEmpty() || $dashboard['upcoming_events']->isNotEmpty())
        <div class="col-lg-6 mb-3">
            <div class="tile h-100">
                <h3 class="dash-section-title"><i class="fa fa-calendar"></i> {{ __('pages.dashboard.upcoming') }}</h3>
                @foreach($dashboard['upcoming_services'] as $service)
                    <div class="dash-list-item d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $service->title }}</strong>
                            <div class="text-muted small">{{ __('pages.dashboard.service_type', ['type' => $service->service_type?->label() ?? __('pages.shared.service')]) }}</div>
                        </div>
                        <span class="badge badge-primary">{{ $service->service_date?->format('M d') }}</span>
                    </div>
                @endforeach
                @foreach($dashboard['upcoming_events'] as $event)
                    <div class="dash-list-item d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $event->title }}</strong>
                            <div class="text-muted small">{{ $event->category?->label() ?? __('pages.shared.event') }}@if($event->venue) — {{ $event->venue }}@endif</div>
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
                <h3 class="dash-section-title"><i class="fa fa-bullhorn"></i> {{ __('pages.dashboard.active_announcements') }}</h3>
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
                    <a href="{{ route('church.announcements.index') }}" class="btn btn-sm btn-link px-0 mt-2">{{ __('pages.dashboard.view_all_announcements') }}</a>
                @endcan
            </div>
        </div>
    @endif
</div>

@if($dashboard['is_administrator'])
    <div class="tile">
        <h3 class="tile-title">{{ __('pages.dashboard.church_information') }}</h3>
        <table class="table table-borderless mb-0">
            <tr><th width="180">{{ __('pages.shared.pastor') }}</th><td>{{ $church->pastor_name ?? '—' }}</td></tr>
            <tr><th>{{ __('common.email') }}</th><td>{{ $church->email }}</td></tr>
            <tr><th>{{ __('common.phone') }}</th><td>{{ $church->phone ?? '—' }}</td></tr>
            <tr><th>{{ __('common.location') }}</th><td>{{ collect([$church->city, $church->country])->filter()->implode(', ') ?: '—' }}</td></tr>
            <tr><th>{{ __('common.status') }}</th><td>{{ ucfirst($church->status->value) }}</td></tr>
            @if($church->activeSubscription)
                <tr><th>{{ __('pages.dashboard.package') }}</th><td>{{ $church->activeSubscription?->package?->name ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.dashboard.subscription') }}</th><td>{{ ucfirst($church->activeSubscription->status->value) }} — {{ ucfirst($church->activeSubscription->billing_cycle->value) }}</td></tr>
            @endif
        </table>
    </div>
@elseif($dashboard['is_pastor'])
    <div class="tile">
        <h3 class="tile-title">{{ __('pages.dashboard.ministry_overview') }}</h3>
        <div class="row">
            <div class="col-md-4">
                <p class="mb-1 text-muted small">{{ __('pages.dashboard.new_members_this_month') }}</p>
                <h4>{{ number_format($stats['new_members_month']) }}</h4>
            </div>
            <div class="col-md-4">
                <p class="mb-1 text-muted small">{{ __('pages.dashboard.children_registered') }}</p>
                <h4>{{ number_format($stats['children']) }}</h4>
            </div>
            <div class="col-md-4">
                <p class="mb-1 text-muted small">{{ __('pages.dashboard.church_contact') }}</p>
                <h6 class="mb-0">{{ $church->email }}</h6>
                <small class="text-muted">{{ $church->phone ?? '—' }}</small>
            </div>
        </div>
    </div>
@elseif($dashboard['is_secretary'])
    <div class="tile">
        <h3 class="tile-title">{{ __('pages.dashboard.administrative_overview') }}</h3>
        <div class="row">
            <div class="col-md-3">
                <p class="mb-1 text-muted small">{{ __('pages.dashboard.new_members_this_month') }}</p>
                <h4>{{ number_format($stats['new_members_month']) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">{{ __('pages.dashboard.departments') }}</p>
                <h4>{{ number_format($stats['departments']) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">{{ __('pages.dashboard.upcoming_services') }}</p>
                <h4>{{ number_format($stats['upcoming_services_count']) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">{{ __('pages.dashboard.upcoming_events') }}</p>
                <h4>{{ number_format($stats['upcoming_events_count']) }}</h4>
            </div>
        </div>
    </div>
@elseif($dashboard['is_treasurer'])
    <div class="tile mb-3">
        <h3 class="tile-title">{{ __('pages.dashboard.financial_overview', ['period' => $dashboard['finance']['period']['label'] ?? now()->format('F Y')]) }}</h3>
        <div class="row">
            <div class="col-md-3">
                <p class="mb-1 text-muted small">{{ __('pages.dashboard.monthly_income') }}</p>
                <h4 class="text-success">{{ $currency }} {{ number_format($stats['monthly_income'] ?? 0, 0) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">{{ __('pages.dashboard.monthly_expenses') }}</p>
                <h4 class="text-danger">{{ $currency }} {{ number_format($stats['monthly_expenses'] ?? 0, 0) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">{{ __('pages.dashboard.net_balance') }}</p>
                <h4>{{ $currency }} {{ number_format($stats['net_income'] ?? 0, 0) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">{{ __('pages.dashboard.active_pledges_label') }}</p>
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
            <h3 class="tile-title">{{ __('pages.dashboard.recent_transactions') }}</h3>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('common.type') }}</th>
                            <th>{{ __('common.description') }}</th>
                            <th>{{ __('common.date') }}</th>
                            <th class="text-right">{{ __('common.amount') }}</th>
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
        <h3 class="tile-title">{{ __('pages.dashboard.accounting_overview', ['period' => $dashboard['finance']['period']['label'] ?? now()->format('F Y')]) }}</h3>
        <div class="row">
            <div class="col-md-3">
                <p class="mb-1 text-muted small">{{ __('pages.dashboard.income_recorded') }}</p>
                <h4 class="text-success">{{ $currency }} {{ number_format($stats['monthly_income'] ?? 0, 0) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">{{ __('pages.dashboard.expenses_recorded') }}</p>
                <h4 class="text-danger">{{ $currency }} {{ number_format($stats['monthly_expenses'] ?? 0, 0) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">{{ __('pages.dashboard.ytd_expenses') }}</p>
                <h4>{{ $currency }} {{ number_format($stats['expenses_year'] ?? 0, 0) }}</h4>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted small">{{ __('pages.dashboard.net_this_month_label') }}</p>
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
            <h3 class="tile-title">{{ __('pages.dashboard.recent_entries') }}</h3>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('common.type') }}</th>
                            <th>{{ __('common.description') }}</th>
                            <th>{{ __('common.date') }}</th>
                            <th class="text-right">{{ __('common.amount') }}</th>
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
