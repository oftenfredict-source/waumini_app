@extends('layouts.auth')

@section('title', 'Set New Password')

@section('topbar_action')
    <a href="{{ route('church.login') }}" class="auth-topbar-link">Back to sign in</a>
@endsection

@section('panel_icon', 'fa-lock')
@section('panel_eyebrow', 'Password reset')
@section('panel_title')
    Choose a new <span>password</span>
@endsection
@section('panel_lead')
    Your identity has been verified. Enter a strong new password for <strong>{{ $loginIdentifier }}</strong>.
@endsection

@section('form_title', 'Set new password')
@section('form_subtitle', 'Use at least 8 characters')

@section('content')
    <form method="POST" action="{{ route('church.password.forgot.reset.submit') }}" novalidate>
        @csrf

        @include('partials.sweetalert-flash')

        <div class="auth-field">
            <label for="new_password">New password</label>
            <div class="auth-input-wrap">
                <i class="fa fa-lock auth-input-icon"></i>
                @include('partials.password-input', [
                    'id' => 'new_password',
                    'name' => 'password',
                    'placeholder' => 'At least 8 characters',
                    'invalid' => $errors->has('password'),
                    'class' => 'auth-input',
                ])
            </div>
            @error('password')<span class="auth-field-error">{{ $message }}</span>@enderror
        </div>

        <div class="auth-field">
            <label for="password_confirmation">Confirm password</label>
            <div class="auth-input-wrap">
                <i class="fa fa-lock auth-input-icon"></i>
                @include('partials.password-input', [
                    'id' => 'password_confirmation',
                    'name' => 'password_confirmation',
                    'placeholder' => 'Repeat new password',
                    'invalid' => $errors->has('password_confirmation'),
                    'class' => 'auth-input',
                ])
            </div>
            @error('password_confirmation')<span class="auth-field-error">{{ $message }}</span>@enderror
        </div>

        <button class="auth-submit" type="submit">
            <i class="fa fa-save"></i> Save new password
        </button>
    </form>
@endsection
