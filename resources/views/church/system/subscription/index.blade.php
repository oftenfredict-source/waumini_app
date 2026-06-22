@extends('layouts.church')

@section('title', 'Upgrade Plan')

@push('styles')
<style>
    .plan-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        height: 100%;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .plan-card:hover {
        border-color: var(--waumini-primary, #940000);
        box-shadow: 0 8px 24px rgba(148, 0, 0, 0.08);
    }
    .plan-card.is-current {
        border-color: var(--waumini-primary, #940000);
        box-shadow: 0 8px 24px rgba(148, 0, 0, 0.12);
    }
    .plan-card.is-recommended {
        position: relative;
    }
    .plan-price {
        font-size: 2rem;
        font-weight: 700;
        color: #2a2c36;
        line-height: 1;
    }
    .plan-price small {
        font-size: 0.9rem;
        font-weight: 500;
        color: #6c757d;
    }
    .current-plan-banner {
        background: #f8f9fc;
        border-left: 4px solid var(--waumini-primary, #940000);
        padding: 1rem 1.25rem;
        border-radius: 0 6px 6px 0;
    }
</style>
@endpush

@section('content')
@include('church.system.partials.nav')

<div class="app-title">
    <div>
        <h1><i class="fa fa-level-up"></i> Upgrade Plan</h1>
        <p>Manage your church subscription for {{ $church->name }}</p>
    </div>
    <div class="text-right">
        <a href="{{ route('church.system.subscription.terms') }}" class="btn btn-outline-secondary">
            <i class="fa fa-file-text-o"></i> Terms &amp; Conditions
        </a>
    </div>
</div>

<div class="current-plan-banner mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5 class="mb-1">Current Plan</h5>
            @if($currentPackage)
                <p class="mb-0">
                    <strong>{{ $currentPackage->name }}</strong>
                    @if($currentSubscription)
                        <span class="badge badge-{{ $currentSubscription->status->value === 'active' ? 'success' : 'info' }} ml-2">
                            {{ ucfirst(str_replace('_', ' ', $currentSubscription->status->value)) }}
                        </span>
                    @endif
                    <br>
                    <span class="text-muted">
                        Billing: Yearly
                        @if($currentSubscription?->ends_at)
                            &middot; Renews/ends {{ $currentSubscription->ends_at->format('M d, Y') }}
                        @endif
                        @if($currentSubscription?->trial_ends_at)
                            &middot; Trial ends {{ $currentSubscription->trial_ends_at->format('M d, Y') }}
                        @endif
                    </span>
                </p>
            @else
                <p class="mb-0 text-muted">No active subscription package is assigned to your church yet.</p>
            @endif
        </div>
        <div class="col-md-4 text-md-right mt-3 mt-md-0">
            <span class="badge badge-light border px-3 py-2">
                Currency: {{ $currency }}
            </span>
        </div>
    </div>
</div>

<div class="row">
    @foreach($packages as $package)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="tile plan-card h-100 @if($package->is_current) is-current @endif">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h3 class="tile-title mb-0">{{ $package->name }}</h3>
                    @if($package->is_current)
                        <span class="badge badge-primary">Current</span>
                    @elseif($package->is_upgrade)
                        <span class="badge badge-success">Upgrade</span>
                    @endif
                </div>

                <p class="text-muted">{{ $package->description }}</p>

                <div class="mb-3">
                    @php
                        $planCurrency = \App\Support\Currency::label($package->currency ?: $currency);
                        $planDecimals = \App\Support\Currency::decimals($package->currency ?: $currency);
                    @endphp
                    <div class="plan-price">
                        {{ $planCurrency }} {{ number_format($package->installation_price, $planDecimals) }}
                        <small>installation</small>
                    </div>
                    <div class="text-muted">
                        then {{ $planCurrency }} {{ number_format($package->yearly_price, $planDecimals) }} /year
                    </div>
                </div>

                <ul class="list-unstyled mb-3">
                    @if($package->max_members)
                        <li><i class="fa fa-users text-primary"></i> Up to {{ number_format($package->max_members) }} members</li>
                    @else
                        <li><i class="fa fa-users text-primary"></i> Unlimited members</li>
                    @endif
                    @if($package->max_sms_monthly)
                        <li><i class="fa fa-comment text-primary"></i> {{ number_format($package->max_sms_monthly) }} SMS/month</li>
                    @endif
                    @if($package->trial_days)
                        <li><i class="fa fa-clock-o text-primary"></i> {{ $package->trial_days }}-day trial for new churches</li>
                    @endif
                </ul>

                @if($package->features->isNotEmpty())
                    <h6 class="text-uppercase text-muted">Features</h6>
                    <ul class="mb-4">
                        @foreach($package->features as $feature)
                            @if($feature->pivot->is_enabled)
                                <li><i class="fa fa-check text-success"></i> {{ $feature->name }}</li>
                            @endif
                        @endforeach
                    </ul>
                @endif

                @if($package->is_current)
                    <button type="button" class="btn btn-secondary btn-block" disabled>
                        <i class="fa fa-check"></i> Current Plan
                    </button>
                @else
                    <form method="POST" action="{{ route('church.system.subscription.upgrade') }}"
                        data-swal-confirm="Upgrade to the {{ $package->name }} plan? Your subscription will be updated immediately.">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $package->id }}">
                        <p class="small text-muted mb-3">
                            Includes a one-time installation fee of
                            <strong>{{ $currency }} {{ number_format($package->installation_price, 2) }}</strong>
                            plus an annual fee of
                            <strong>{{ $currency }} {{ number_format($package->yearly_price, 2) }}</strong>.
                        </p>
                        <div class="form-check mb-3">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="accept_terms" value="1" required>
                                I agree to the <a href="{{ route('church.system.subscription.terms') }}" target="_blank">Terms &amp; Conditions</a>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-level-up"></i>
                            @if($package->is_upgrade)
                                Upgrade to {{ $package->name }}
                            @else
                                Switch to {{ $package->name }}
                            @endif
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
