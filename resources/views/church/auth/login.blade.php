@extends('layouts.auth')

@section('title', 'Church Sign In')

@section('topbar_action')
    <a href="{{ route('landing') }}" class="auth-topbar-link">Back to home</a>
@endsection

@section('panel_icon', 'fa-building')
@section('panel_eyebrow', 'Church portal')
@section('panel_title')
    Welcome <span>back</span>
@endsection
@section('panel_lead')
    Sign in with your church admin email or member ID to access your dashboard, membership tools, and church services.
@endsection

@section('panel_features')
    <div class="auth-feature">
        <i class="fa fa-users"></i>
        <div>
            <strong>Members & leaders</strong>
            <span>Manage membership, departments, and church records in one place.</span>
        </div>
    </div>
    <div class="auth-feature">
        <i class="fa fa-lock"></i>
        <div>
            <strong>Secure access</strong>
            <span>Church admins use email. Members sign in with their unique member ID.</span>
        </div>
    </div>
@endsection

@section('form_title', 'Church sign in')
@section('form_subtitle', 'Enter your credentials to continue')

@section('content')
    <form method="POST" action="{{ route('church.login.submit') }}" novalidate>
        @csrf

        @if(!empty($ownerSessionActive))
            <div class="auth-alert auth-alert-info">
                You are signed in to the owner dashboard. Signing in here will switch to your church account.
            </div>
        @endif

        @include('partials.sweetalert-flash')

        <div class="auth-field">
            <label for="login_email">Email or member ID</label>
            <div class="auth-input-wrap">
                <i class="fa fa-user auth-input-icon"></i>
                <input id="login_email"
                    class="form-control auth-input @error('email') is-invalid @enderror"
                    type="text"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="admin@church.org or IM-2026-0001"
                    autofocus
                    required>
            </div>
            @error('email')<span class="auth-field-error">{{ $message }}</span>@enderror
            <span class="auth-field-hint">Church admins use email. Members use their member ID (e.g. IM-2026-0001).</span>
        </div>

        <div class="auth-field">
            <label for="login_password">Password</label>
            <div class="auth-input-wrap">
                <i class="fa fa-lock auth-input-icon"></i>
                @include('partials.password-input', [
                    'id' => 'login_password',
                    'invalid' => $errors->has('password'),
                    'class' => 'auth-input',
                ])
            </div>
            @error('password')<span class="auth-field-error">{{ $message }}</span>@enderror
        </div>

        <div class="auth-options">
            <label class="auth-remember">
                <input type="checkbox" name="remember" @checked(old('remember'))>
                <span>Stay signed in</span>
            </label>
        </div>

        <button class="auth-submit" type="submit">
            <i class="fa fa-sign-in"></i> Sign in
        </button>
    </form>
@endsection

@section('auth_footer')
    <p>New member? <a href="{{ route('church.register') }}">Register now</a></p>
    <p>Platform owner? <a href="{{ route('owner.login') }}">Owner login</a></p>
@endsection
