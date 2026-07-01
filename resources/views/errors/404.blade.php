@extends('errors.layout')

@section('title', __('errors.page_not_found'))

@section('content')
    <div class="error-code-wrap">
        <div class="error-code-glow" aria-hidden="true"></div>
        <p class="error-code" aria-hidden="true">404</p>
    </div>

    <div class="error-card">
        <div class="error-eyebrow">
            <i class="fa fa-compass"></i>
            {{ __('errors.eyebrow') }}
        </div>

        <h1>{{ __('errors.heading') }}</h1>
        <p class="error-lead">
            {{ __('errors.lead') }}
        </p>

        <div class="error-actions">
            <a href="{{ route('landing') }}" class="error-btn error-btn-primary">
                <i class="fa fa-home"></i> {{ __('errors.go_to_homepage') }}
            </a>
            <a href="{{ route('church.login') }}" class="error-btn error-btn-ghost">
                <i class="fa fa-sign-in"></i> {{ __('errors.church_sign_in') }}
            </a>
            <button type="button" class="error-btn error-btn-ghost" onclick="history.back()">
                <i class="fa fa-arrow-left"></i> {{ __('errors.go_back') }}
            </button>
        </div>

        <ul class="error-hints">
            <li class="error-hint">
                <i class="fa fa-link"></i>
                <div>
                    <strong>{{ __('errors.hint_check_url') }}</strong>
                    <span>{{ __('errors.hint_check_url_desc') }}</span>
                </div>
            </li>
            <li class="error-hint">
                <i class="fa fa-user"></i>
                <div>
                    <strong>{{ __('errors.hint_dashboard') }}</strong>
                    <span>{{ __('errors.hint_dashboard_desc') }}</span>
                </div>
            </li>
        </ul>
    </div>
@endsection
