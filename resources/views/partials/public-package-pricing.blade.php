@php
    use App\Support\Currency;

    $code = strtoupper($package->currency ?: ($currencyCode ?? 'TZS'));
    $label = Currency::label($code);
    $decimals = Currency::decimals($code);
@endphp

<div class="landing-price-box">
    <div class="landing-price-main">
        {{ $label }} {{ number_format($package->installation_price, $decimals) }}
        <small>one-time installation</small>
    </div>
    <div class="landing-price-sub">
        then {{ $label }} {{ number_format($package->yearly_price, $decimals) }} / year
    </div>
    @if($package->trial_days)
        <div class="landing-price-trial">{{ $package->trial_days }}-day free trial included</div>
    @endif
</div>
