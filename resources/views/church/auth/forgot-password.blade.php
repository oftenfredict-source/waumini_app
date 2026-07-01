@extends('layouts.auth')

@section('title', __('auth.forgot_password_title'))

@section('topbar_action')
    <a href="{{ route('church.login') }}" class="auth-topbar-link">{{ __('auth.back_to_sign_in') }}</a>
@endsection

@section('panel_icon', 'fa-key')
@section('panel_eyebrow', __('auth.account_recovery'))
@section('panel_title')
    {!! __('auth.reset_password_panel') !!}
@endsection
@section('panel_lead')
    {{ __('auth.forgot_password_lead') }}
@endsection

@section('panel_features')
    <div class="auth-feature">
        <i class="fa fa-mobile"></i>
        <div>
            <strong>{{ __('auth.sms_verification') }}</strong>
            <span>{{ __('auth.sms_verification_desc') }}</span>
        </div>
    </div>
    <div class="auth-feature">
        <i class="fa fa-life-ring"></i>
        <div>
            <strong>{{ __('auth.need_help') }}</strong>
            <span>{{ __('auth.need_help_desc') }}</span>
        </div>
    </div>
@endsection

@section('form_title', __('auth.forgot_password_form'))
@section('form_subtitle', __('auth.forgot_password_subtitle'))

@section('content')
    <form method="POST" action="{{ route('church.password.forgot.send') }}" novalidate>
        @csrf

        @include('partials.sweetalert-flash')

        <div class="auth-field">
            <label for="forgot_email">{{ __('auth.email_or_member_id') }}</label>
            <div class="auth-input-wrap">
                <i class="fa fa-user auth-input-icon"></i>
                <input id="forgot_email"
                    class="form-control auth-input @error('email') is-invalid @enderror"
                    type="text"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="{{ __('auth.email_placeholder') }}"
                    autofocus
                    required>
            </div>
            @error('email')<span class="auth-field-error">{{ $message }}</span>@enderror
            <span class="auth-field-hint">{{ __('auth.use_same_identifier') }}</span>
        </div>

        <button class="auth-submit" type="submit">
            <i class="fa fa-paper-plane"></i> {{ __('auth.send_verification_code') }}
        </button>
    </form>

    <a href="{{ route('church.login') }}" class="auth-back-link">
        <i class="fa fa-arrow-left"></i> {{ __('auth.back_to_sign_in') }}
    </a>
@endsection

@section('auth_footer')
    <p>{{ __('auth.new_member') }} <a href="{{ route('church.register') }}">{{ __('auth.register_now') }}</a></p>
@endsection
