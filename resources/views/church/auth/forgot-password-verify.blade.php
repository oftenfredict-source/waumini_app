@extends('layouts.auth')

@section('title', 'Verify Reset Code')

@section('topbar_action')
    <a href="{{ route('church.password.forgot') }}" class="auth-topbar-link">Start over</a>
@endsection

@section('panel_icon', 'fa-mobile')
@section('panel_eyebrow', 'Password reset')
@section('panel_title')
    Check your <span>phone</span>
@endsection
@section('panel_lead')
    We sent a 6-digit verification code to the phone number linked to your account. Enter it below to continue.
@endsection

@section('form_title', 'Enter verification code')
@section('form_subtitle')
    Code sent for <strong>{{ $loginIdentifier }}</strong>
    @if($otpExpiresAt)
        · expires at {{ $otpExpiresAt->format('H:i') }}
    @endif
@endsection

@section('content')
    <form method="POST" action="{{ route('church.password.forgot.verify.submit') }}" novalidate>
        @csrf

        @include('partials.sweetalert-flash')

        <div class="auth-field">
            <label for="otp_code">Verification code</label>
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
                    placeholder="000000">
            </div>
            @error('otp')<span class="auth-field-error">{{ $message }}</span>@enderror
        </div>

        <button class="auth-submit" type="submit">
            <i class="fa fa-check"></i> Verify code
        </button>
    </form>

    @if($resendAttempts < $maxResendAttempts)
        <form method="POST" action="{{ route('church.password.forgot.resend') }}" class="auth-resend">
            @csrf
            <button type="submit" class="btn btn-link">Resend code</button>
            <small class="text-muted d-block">
                {{ $maxResendAttempts - $resendAttempts }} resend(s) remaining
            </small>
        </form>
    @endif

    <a href="{{ route('church.password.forgot') }}" class="auth-back-link">
        <i class="fa fa-arrow-left"></i> Start over
    </a>
@endsection
