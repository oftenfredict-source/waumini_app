@extends('layouts.auth')

@section('title', __('owner.owner_sign_in'))

@section('topbar_action')
    <a href="{{ route('church.login') }}" class="auth-topbar-link">{{ __('owner.church_login') }}</a>
@endsection

@section('panel_icon', 'fa-cogs')
@section('panel_eyebrow', __('owner.platform_owner'))
@section('panel_title')
    {!! __('owner.panel_title') !!}
@endsection
@section('panel_lead')
    {{ __('owner.panel_lead') }}
@endsection

@section('panel_features')
    <div class="auth-feature">
        <i class="fa fa-building"></i>
        <div>
            <strong>{{ __('owner.church_management') }}</strong>
            <span>{{ __('owner.church_management_desc') }}</span>
        </div>
    </div>
    <div class="auth-feature">
        <i class="fa fa-shield"></i>
        <div>
            <strong>{{ __('owner.restricted_access') }}</strong>
            <span>{{ __('owner.restricted_access_desc') }}</span>
        </div>
    </div>
@endsection

@section('form_title', __('owner.form_title'))
@section('form_subtitle', __('owner.form_subtitle'))

@section('content')
    <form method="POST" action="{{ route('owner.login.submit') }}" novalidate>
        @csrf

        @include('partials.sweetalert-flash')

        <div class="auth-field">
            <label for="owner_email">{{ __('owner.email_address') }}</label>
            <div class="auth-input-wrap">
                <i class="fa fa-envelope auth-input-icon"></i>
                <input id="owner_email"
                    class="form-control auth-input @error('email') is-invalid @enderror"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="{{ __('owner.email_placeholder') }}"
                    autofocus
                    required>
            </div>
            @error('email')<span class="auth-field-error">{{ $message }}</span>@enderror
        </div>

        <div class="auth-field">
            <label for="owner_password">{{ __('auth.password') }}</label>
            <div class="auth-input-wrap">
                <i class="fa fa-lock auth-input-icon"></i>
                @include('partials.password-input', [
                    'id' => 'owner_password',
                    'placeholder' => __('owner.password_placeholder'),
                    'invalid' => $errors->has('password'),
                    'class' => 'auth-input',
                ])
            </div>
            @error('password')<span class="auth-field-error">{{ $message }}</span>@enderror
        </div>

        <div class="auth-options">
            <label class="auth-remember">
                <input type="checkbox" name="remember" @checked(old('remember'))>
                <span>{{ __('auth.stay_signed_in') }}</span>
            </label>
        </div>

        <button class="auth-submit" type="submit">
            <i class="fa fa-sign-in"></i> {{ __('common.sign_in') }}
        </button>
    </form>
@endsection

@section('auth_footer')
    <p>{{ __('owner.church_staff_footer') }} <a href="{{ route('church.login') }}">{{ __('owner.go_to_church_login') }}</a></p>
@endsection
