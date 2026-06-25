@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('topbar_action')
    <a href="{{ route('church.login') }}" class="auth-topbar-link">Back to sign in</a>
@endsection

@section('panel_icon', 'fa-key')
@section('panel_eyebrow', 'Account recovery')
@section('panel_title')
    Reset your <span>password</span>
@endsection
@section('panel_lead')
    Enter the email or member ID linked to your account. We will send a verification code to the phone number on file.
@endsection

@section('panel_features')
    <div class="auth-feature">
        <i class="fa fa-mobile"></i>
        <div>
            <strong>SMS verification</strong>
            <span>A 6-digit code is sent to your registered phone number for security.</span>
        </div>
    </div>
    <div class="auth-feature">
        <i class="fa fa-life-ring"></i>
        <div>
            <strong>Need help?</strong>
            <span>If you do not have a phone on file, contact your church administrator.</span>
        </div>
    </div>
@endsection

@section('form_title', 'Forgot password')
@section('form_subtitle', 'We will verify your identity before you set a new password')

@section('content')
    <form method="POST" action="{{ route('church.password.forgot.send') }}" novalidate>
        @csrf

        @include('partials.sweetalert-flash')

        <div class="auth-field">
            <label for="forgot_email">Email or member ID</label>
            <div class="auth-input-wrap">
                <i class="fa fa-user auth-input-icon"></i>
                <input id="forgot_email"
                    class="form-control auth-input @error('email') is-invalid @enderror"
                    type="text"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="admin@church.org or IM-2026-0001"
                    autofocus
                    required>
            </div>
            @error('email')<span class="auth-field-error">{{ $message }}</span>@enderror
            <span class="auth-field-hint">Use the same identifier you use to sign in.</span>
        </div>

        <button class="auth-submit" type="submit">
            <i class="fa fa-paper-plane"></i> Send verification code
        </button>
    </form>

    <a href="{{ route('church.login') }}" class="auth-back-link">
        <i class="fa fa-arrow-left"></i> Back to sign in
    </a>
@endsection

@section('auth_footer')
    <p>New member? <a href="{{ route('church.register') }}">Register now</a></p>
@endsection
