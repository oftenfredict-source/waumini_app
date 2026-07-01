@extends('layouts.auth')

@section('title', __('auth.set_new_password_title'))

@section('topbar_action')
    <a href="{{ route('church.login') }}" class="auth-topbar-link">{{ __('auth.back_to_sign_in') }}</a>
@endsection

@section('panel_icon', 'fa-lock')
@section('panel_eyebrow', __('auth.password_reset'))
@section('panel_title')
    {!! __('auth.choose_new_password') !!}
@endsection
@section('panel_lead')
    {!! __('auth.set_password_lead', ['identifier' => e($loginIdentifier)]) !!}
@endsection

@section('form_title', __('auth.set_new_password_form'))
@section('form_subtitle', __('auth.use_at_least_8'))

@section('content')
    <form method="POST" action="{{ route('church.password.forgot.reset.submit') }}" novalidate>
        @csrf

        @include('partials.sweetalert-flash')

        <div class="auth-field">
            <label for="new_password">{{ __('auth.new_password') }}</label>
            <div class="auth-input-wrap">
                <i class="fa fa-lock auth-input-icon"></i>
                @include('partials.password-input', [
                    'id' => 'new_password',
                    'name' => 'password',
                    'placeholder' => __('auth.at_least_8_placeholder'),
                    'invalid' => $errors->has('password'),
                    'class' => 'auth-input',
                ])
            </div>
            @error('password')<span class="auth-field-error">{{ $message }}</span>@enderror
        </div>

        <div class="auth-field">
            <label for="password_confirmation">{{ __('auth.confirm_password') }}</label>
            <div class="auth-input-wrap">
                <i class="fa fa-lock auth-input-icon"></i>
                @include('partials.password-input', [
                    'id' => 'password_confirmation',
                    'name' => 'password_confirmation',
                    'placeholder' => __('auth.repeat_password_placeholder'),
                    'invalid' => $errors->has('password_confirmation'),
                    'class' => 'auth-input',
                ])
            </div>
            @error('password_confirmation')<span class="auth-field-error">{{ $message }}</span>@enderror
        </div>

        <button class="auth-submit" type="submit">
            <i class="fa fa-save"></i> {{ __('auth.save_new_password') }}
        </button>
    </form>
@endsection
