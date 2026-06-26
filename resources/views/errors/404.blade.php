@extends('errors.layout')

@section('title', 'Page Not Found')

@section('content')
    <div class="error-code-wrap">
        <div class="error-code-glow" aria-hidden="true"></div>
        <p class="error-code" aria-hidden="true">404</p>
    </div>

    <div class="error-card">
        <div class="error-eyebrow">
            <i class="fa fa-compass"></i>
            Page not found
        </div>

        <h1>This page doesn’t exist</h1>
        <p class="error-lead">
            The link may be broken, outdated, or the page may have been moved.
            Let’s get you back on track.
        </p>

        <div class="error-actions">
            <a href="{{ route('landing') }}" class="error-btn error-btn-primary">
                <i class="fa fa-home"></i> Go to homepage
            </a>
            <a href="{{ route('church.login') }}" class="error-btn error-btn-ghost">
                <i class="fa fa-sign-in"></i> Church sign in
            </a>
            <button type="button" class="error-btn error-btn-ghost" onclick="history.back()">
                <i class="fa fa-arrow-left"></i> Go back
            </button>
        </div>

        <ul class="error-hints">
            <li class="error-hint">
                <i class="fa fa-link"></i>
                <div>
                    <strong>Check the URL</strong>
                    <span>Make sure the web address is spelled correctly.</span>
                </div>
            </li>
            <li class="error-hint">
                <i class="fa fa-user"></i>
                <div>
                    <strong>Looking for your church dashboard?</strong>
                    <span>Sign in with your church admin email or member ID.</span>
                </div>
            </li>
        </ul>
    </div>
@endsection
