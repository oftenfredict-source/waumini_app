@extends('layouts.auth')

@section('title', 'Verify Code')

@section('topbar_action')
    <a href="{{ route('church.login') }}" class="auth-topbar-link">Back to sign in</a>
@endsection

@section('panel_icon', 'fa-mobile')
@section('panel_eyebrow', 'Two-step verification')
@section('panel_title')
    Check your <span>phone</span>
@endsection
@section('panel_lead')
    We sent a 6-digit verification code to the phone number linked to your account. Enter it below to complete sign in.
@endsection

@section('form_title', 'Enter verification code')
@section('form_subtitle')
    Code sent for <strong>{{ $loginIdentifier }}</strong>
    @if($otpExpiresAt)
        · expires at {{ $otpExpiresAt->format('H:i') }}
    @endif
@endsection

@section('content')
    <form method="POST" action="{{ route('church.login.otp.verify') }}" novalidate>
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
            <i class="fa fa-check"></i> Verify &amp; sign in
        </button>
    </form>

    @if($resendAttempts < $maxResendAttempts)
        <form method="POST" action="{{ route('church.login.otp.resend') }}" class="auth-resend">
            @csrf
            <button type="submit" class="btn btn-link">Resend code</button>
            <small class="text-muted d-block">
                {{ $maxResendAttempts - $resendAttempts }} resend(s) remaining
            </small>
        </form>
    @endif

    <a href="{{ route('church.login') }}" class="auth-back-link">
        <i class="fa fa-arrow-left"></i> Back to sign in
    </a>
@endsection
