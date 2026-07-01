@extends('layouts.auth')

@section('title', __('auth.verify_reset_title'))

@section('topbar_action')
    <a href="{{ route('church.password.forgot') }}" class="auth-topbar-link">{{ __('auth.start_over') }}</a>
@endsection

@section('panel_icon', 'fa-mobile')
@section('panel_eyebrow', __('auth.password_reset'))
@section('panel_title')
    {!! __('auth.check_your_phone') !!}
@endsection
@section('panel_lead')
    {{ __('auth.verify_reset_lead') }}
@endsection

@section('form_title', __('auth.enter_verification_code'))
@section('form_subtitle')
    {!! __('auth.code_sent_for', ['identifier' => e($loginIdentifier)]) !!}
    @if($otpExpiresAt)
        {{ __('auth.expires_at', ['time' => $otpExpiresAt->format('H:i')]) }}
    @endif
@endsection

@section('content')
    <form method="POST" action="{{ route('church.password.forgot.verify.submit') }}" novalidate>
        @csrf

        @include('partials.sweetalert-flash')

        <div class="auth-field">
            <label for="otp_code">{{ __('auth.verification_code') }}</label>
            <div class="auth-input-wrap">
                <i class="fa fa-key auth-input-icon"></i>
                <input id="otp_code"
                    class="form-control auth-input auth-otp-input @error('otp') is-invalid @enderror"
                    type="text"
                    name="otp"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    autofocus
                    required
                    placeholder="{{ __('auth.otp_placeholder') }}">
            </div>
            @error('otp')<span class="auth-field-error">{{ $message }}</span>@enderror
        </div>

        <button class="auth-submit" type="submit">
            <i class="fa fa-check"></i> {{ __('auth.verify_code') }}
        </button>
    </form>

    @if($resendAttempts < $maxResendAttempts)
        <form method="POST" action="{{ route('church.password.forgot.resend') }}" class="auth-resend">
            @csrf
            <button type="submit" class="btn btn-link">{{ __('auth.resend_code') }}</button>
            <small class="text-muted d-block">
                {{ trans_choice('auth.resends_remaining', $maxResendAttempts - $resendAttempts, ['count' => $maxResendAttempts - $resendAttempts]) }}
            </small>
        </form>
    @endif

    <a href="{{ route('church.password.forgot') }}" class="auth-back-link">
        <i class="fa fa-arrow-left"></i> {{ __('auth.start_over') }}
    </a>
@endsection
