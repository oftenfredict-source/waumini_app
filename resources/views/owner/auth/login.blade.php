@extends('layouts.auth')

@section('title', 'Owner Sign In')

@section('topbar_action')
    <a href="{{ route('church.login') }}" class="auth-topbar-link">Church login</a>
@endsection

@section('panel_icon', 'fa-cogs')
@section('panel_eyebrow', 'Platform owner')
@section('panel_title')
    Owner <span>dashboard</span>
@endsection
@section('panel_lead')
    Sign in to manage churches, subscriptions, platform settings, and system-wide administration.
@endsection

@section('panel_features')
    <div class="auth-feature">
        <i class="fa fa-building"></i>
        <div>
            <strong>Church management</strong>
            <span>Create churches, assign packages, and monitor platform activity.</span>
        </div>
    </div>
    <div class="auth-feature">
        <i class="fa fa-shield"></i>
        <div>
            <strong>Restricted access</strong>
            <span>This area is for platform owners and authorized administrators only.</span>
        </div>
    </div>
@endsection

@section('form_title', 'Owner sign in')
@section('form_subtitle', 'Use your owner account credentials')

@section('content')
    <form method="POST" action="{{ route('owner.login.submit') }}" novalidate>
        @csrf

        @include('partials.sweetalert-flash')

        <div class="auth-field">
            <label for="owner_email">Email address</label>
            <div class="auth-input-wrap">
                <i class="fa fa-envelope auth-input-icon"></i>
                <input id="owner_email"
                    class="form-control auth-input @error('email') is-invalid @enderror"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="admin@example.com"
                    autofocus
                    required>
            </div>
            @error('email')<span class="auth-field-error">{{ $message }}</span>@enderror
        </div>

        <div class="auth-field">
            <label for="owner_password">Password</label>
            <div class="auth-input-wrap">
                <i class="fa fa-lock auth-input-icon"></i>
                @include('partials.password-input', [
                    'id' => 'owner_password',
                    'placeholder' => 'Your password',
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
    <p>Church staff or member? <a href="{{ route('church.login') }}">Go to church login</a></p>
@endsection
