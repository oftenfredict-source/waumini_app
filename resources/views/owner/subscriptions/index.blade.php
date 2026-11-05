@extends('layouts.owner')

@section('title', __('owner.subs.title'))

@section('content')
@php
    $currency = \App\Models\SystemSetting::platformCurrency();
    $statusBadge = function (string $status): string {
        return match ($status) {
            'active' => 'success',
            'trial' => 'info',
            'past_due' => 'warning',
            'expired', 'cancelled' => 'secondary',
            default => 'secondary',
        };
    };
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">{{ __('owner.subs.title') }}</h1>
        <p class="text-muted mb-0">{{ __('owner.subs.subtitle') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('owner.subscriptions.packages') }}" class="btn btn-outline-secondary">
            <i class="bi bi-tags me-1"></i> {{ __('owner.subs.view_packages') }}
        </a>
        <a href="{{ route('owner.settings.index', ['tab' => 'packages']) }}" class="btn btn-primary">
            <i class="bi bi-gear me-1"></i> {{ __('owner.subs.manage_settings') }}
        </a>
    </div>
</div>

<div class="alert border-0 mb-4" style="background:rgba(148,0,0,.06);color:#4a1a1a;">
    <i class="bi bi-info-circle me-1" style="color:#940000;"></i>
    {{ __('owner.subs.billing_note') }}
</div>

<div class="row g-3 g-lg-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.subs.stat_packages') }}</p>
                        <div class="h3 mb-0">{{ number_format($stats['packages']) }}</div>
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
                        <p class="h6 mb-0 text-muted">{{ __('owner.subs.stat_active') }}</p>
                        <div class="h3 mb-0">{{ number_format($stats['active']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(8,145,178,.12);color:#0891b2;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.subs.stat_trial') }}</p>
                        <div class="h3 mb-0">{{ number_format($stats['trial']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(113,113,122,.12);color:#52525b;">
                        <i class="bi bi-slash-circle"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.subs.stat_other') }}</p>
                        <div class="h3 mb-0">{{ number_format($stats['expired']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 g-lg-4 mb-4">
    @forelse($packages as $package)
        <div class="col-md-6 col-xl-4">
            <div class="card owner-panel-card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h3 class="h5 mb-1">{{ $package->name }}</h3>
                            @if($package->is_active)
                                <span class="badge text-bg-success">{{ __('common.active') }}</span>
                            @else
                                <span class="badge text-bg-secondary">{{ __('common.inactive') }}</span>
                            @endif
                        </div>
                        <div class="stats-icon bg-primary bg-opacity-10 text-primary" style="width:40px;height:40px;font-size:1rem;">
                            <i class="bi bi-credit-card"></i>
                        </div>
                    </div>

                    <p class="text-muted small mb-3">{{ $package->description ?: '—' }}</p>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-baseline mb-1">
                            <span class="text-muted small">{{ __('owner.subs.installation') }}</span>
                            <strong style="color:#940000;">{{ $currency }} {{ number_format($package->installation_price, 0) }}</strong>
                        </div>
                        <div class="small text-muted mb-2">{{ __('owner.subs.covers_year_1') }}</div>
                        <div class="d-flex justify-content-between align-items-baseline">
                            <span class="text-muted small">{{ __('owner.subs.yearly') }}</span>
                            <strong>{{ $currency }} {{ number_format($package->yearly_price, 0) }}</strong>
                        </div>
                        <div class="small text-muted">
                            {{ __('owner.subs.then_yearly', ['amount' => $currency.' '.number_format($package->yearly_price, 0)]) }}
                        </div>
                    </div>

                    <div class="mt-auto pt-2 border-top">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge rounded-pill" style="background:rgba(148,0,0,.1);color:#940000;">
                                {{ __('owner.subs.active_churches', ['count' => $package->active_count]) }}
                            </span>
                            <span class="badge rounded-pill text-bg-light border">
                                {{ __('owner.subs.trial_days', ['days' => $package->trial_days]) }}
                            </span>
                            @if($package->trial_count)
                                <span class="badge rounded-pill text-bg-info">
                                    {{ __('owner.subs.on_trial', ['count' => $package->trial_count]) }}
                                </span>
                            @endif
                        </div>
                        <a href="{{ route('owner.settings.index', ['tab' => 'packages']) }}" class="btn btn-sm btn-outline-primary w-100">
                            {{ __('owner.subs.manage_settings') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card owner-panel-card">
                <div class="card-body text-center text-muted py-5">
                    {{ __('owner.set.no_packages') }}
                </div>
            </div>
        </div>
    @endforelse
</div>

<div class="card owner-panel-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ __('owner.subs.church_subscriptions') }}</span>
        <span class="badge text-bg-light border">{{ $subscriptions->total() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('owner.church_label') }}</th>
                        <th>{{ __('owner.package') }}</th>
                        <th>{{ __('owner.church.billing') }}</th>
                        <th>{{ __('owner.status') }}</th>
                        <th>{{ __('owner.church.starts') }}</th>
                        <th>{{ __('owner.subs.ends_trial') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td>
                                @if($subscription->church)
                                    <a href="{{ route('owner.churches.show', $subscription->church) }}" class="fw-semibold text-decoration-none" style="color:#940000;">
                                        {{ $subscription->church->name }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $subscription->package?->name ?? '—' }}</td>
                            <td>{{ ucfirst($subscription->billing_cycle->value) }}</td>
                            <td>
                                <span class="badge text-bg-{{ $statusBadge($subscription->status->value) }}">
                                    {{ ucfirst(str_replace('_', ' ', $subscription->status->value)) }}
                                </span>
                            </td>
                            <td>{{ $subscription->starts_at?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ ($subscription->trial_ends_at ?? $subscription->ends_at)?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">{{ __('owner.subs.no_subscriptions') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($subscriptions->hasPages())
        <div class="card-footer bg-white">
            {{ $subscriptions->links() }}
        </div>
    @endif
</div>
@endsection
