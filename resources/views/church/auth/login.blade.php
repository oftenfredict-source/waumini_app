@extends('layouts.auth')

@section('title', __('auth.church_sign_in'))

@section('topbar_action')
    <a href="{{ route('landing') }}" class="auth-topbar-link">{{ __('common.back_to_home') }}</a>
@endsection

@section('panel_icon', 'fa-building')
@section('panel_eyebrow', __('auth.church_portal'))
@section('panel_title')
    {{ __('auth.welcome_back') }}
@endsection
@section('panel_lead')
    {{ __('auth.church_login_lead') }}
@endsection

@section('panel_features')
    <div class="auth-feature">
        <i class="fa fa-users"></i>
        <div>
            <strong>{{ __('auth.members_leaders') }}</strong>
            <span>{{ __('auth.members_leaders_desc') }}</span>
        </div>
    </div>
    <div class="auth-feature">
        <i class="fa fa-lock"></i>
        <div>
            <strong>{{ __('auth.secure_access') }}</strong>
            <span>{{ __('auth.secure_access_desc') }}</span>
        </div>
    </div>
@endsection

@section('form_title', __('auth.church_sign_in_form'))
@section('form_subtitle', __('auth.enter_credentials'))

@section('content')
    <form method="POST" action="{{ route('church.login.submit') }}" novalidate>
        @csrf

        @if(!empty($ownerSessionActive))
            <div class="auth-alert auth-alert-info">
                {{ __('auth.owner_session_active') }}
            </div>
        @endif

        @include('partials.sweetalert-flash')

        <div class="auth-field">
            <label for="login_email">{{ __('auth.email_or_member_id') }}</label>
            <div class="auth-input-wrap">
                <i class="fa fa-user auth-input-icon"></i>
                <input id="login_email"
                    class="form-control auth-input @error('email') is-invalid @enderror"
                    type="text"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="{{ __('auth.email_placeholder') }}"
                    autofocus
                    required>
            </div>
            @error('email')<span class="auth-field-error">{{ $message }}</span>@enderror
            <span class="auth-field-hint">{{ __('auth.email_hint') }}</span>
        </div>

        <div class="auth-field">
            <label for="login_password">{{ __('auth.password') }}</label>
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
                <span>{{ __('auth.stay_signed_in') }}</span>
            </label>
            <a href="{{ route('church.password.forgot') }}" class="auth-forgot-link">{{ __('auth.forgot_password') }}</a>
        </div>

        <button class="auth-submit" type="submit">
            <i class="fa fa-sign-in"></i> {{ __('common.sign_in') }}
        </button>
    </form>
@endsection

@section('auth_footer')
    <p>{{ __('auth.new_member') }} <a href="{{ route('church.register') }}">{{ __('auth.register_now') }}</a></p>
@endsection
