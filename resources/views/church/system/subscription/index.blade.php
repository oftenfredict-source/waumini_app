@extends('layouts.church')

@section('title', __('pages.system_subscription.title'))

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
        <h1><i class="fa fa-level-up"></i> {{ __('pages.system_subscription.title') }}</h1>
        <p>{{ __('pages.system_subscription.subtitle', ['church' => $church->name]) }}</p>
    </div>
    <div class="text-right">
        <a href="{{ route('church.system.subscription.terms') }}" class="btn btn-outline-secondary">
            <i class="fa fa-file-text-o"></i> {{ __('pages.system_subscription.terms') }}
        </a>
    </div>
</div>

<div class="current-plan-banner mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5 class="mb-1">{{ __('pages.system_subscription.current_plan') }}</h5>
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
                        {{ __('pages.system_subscription.billing_yearly') }}
                        @if($currentSubscription?->ends_at)
                            &middot; {{ __('pages.system_subscription.renews_ends', ['date' => $currentSubscription->ends_at->format('M d, Y')]) }}
                        @endif
                        @if($currentSubscription?->trial_ends_at)
                            &middot; {{ __('pages.system_subscription.trial_ends', ['date' => $currentSubscription->trial_ends_at->format('M d, Y')]) }}
                        @endif
                    </span>
                </p>
            @else
                <p class="mb-0 text-muted">{{ __('pages.system_subscription.no_package_assigned') }}</p>
            @endif
        </div>
        <div class="col-md-4 text-md-right mt-3 mt-md-0">
            <span class="badge badge-light border px-3 py-2">
                {{ __('pages.system_subscription.currency_label', ['currency' => $currency]) }}
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
                        <span class="badge badge-primary">{{ __('pages.system_subscription.current') }}</span>
                    @elseif($package->is_upgrade)
                        <span class="badge badge-success">{{ __('pages.system_subscription.upgrade') }}</span>
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
                        <small>{{ __('pages.system_subscription.installation') }}</small>
                    </div>
                    <div class="text-muted">
                        {{ __('pages.system_subscription.then_yearly', ['amount' => $planCurrency . ' ' . number_format($package->yearly_price, $planDecimals)]) }}
                    </div>
                </div>

                <ul class="list-unstyled mb-3">
                    @if($package->max_members)
                        <li><i class="fa fa-users text-primary"></i> {{ __('pages.system_subscription.members_limit', ['count' => number_format($package->max_members)]) }}</li>
                    @else
                        <li><i class="fa fa-users text-primary"></i> {{ __('pages.system_subscription.unlimited_members') }}</li>
                    @endif
                    @if($package->max_sms_monthly)
                        <li><i class="fa fa-comment text-primary"></i> {{ __('pages.system_subscription.sms_per_month', ['count' => number_format($package->max_sms_monthly)]) }}</li>
                    @endif
                    @if($package->trial_days)
                        <li><i class="fa fa-clock-o text-primary"></i> {{ __('pages.system_subscription.trial_days', ['days' => $package->trial_days]) }}</li>
                    @endif
                </ul>

                @if($package->features->isNotEmpty())
                    <h6 class="text-uppercase text-muted">{{ __('pages.system_subscription.features') }}</h6>
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
                        <i class="fa fa-check"></i> {{ __('pages.system_subscription.current_plan_btn') }}
                    </button>
                @else
                    <form method="POST" action="{{ route('church.system.subscription.upgrade') }}"
                        data-swal-confirm="{{ __('pages.system_subscription.upgrade_confirm', ['name' => $package->name]) }}">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $package->id }}">
                        <p class="small text-muted mb-3">
                            {{ __('pages.system_subscription.fee_disclaimer', [
                                'installation' => $currency . ' ' . number_format($package->installation_price, 2),
                                'yearly' => $currency . ' ' . number_format($package->yearly_price, 2),
                            ]) }}
                        </p>
                        <div class="form-check mb-3">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="accept_terms" value="1" required>
                                {!! __('pages.system_subscription.agree_terms', [
                                    'link' => '<a href="' . route('church.system.subscription.terms') . '" target="_blank">' . e(__('pages.system_subscription.terms')) . '</a>',
                                ]) !!}
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-level-up"></i>
                            @if($package->is_upgrade)
                                {{ __('pages.system_subscription.upgrade_to', ['name' => $package->name]) }}
                            @else
                                {{ __('pages.system_subscription.switch_to', ['name' => $package->name]) }}
                            @endif
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
