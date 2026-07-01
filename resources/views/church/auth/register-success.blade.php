@extends('layouts.church-register')

@section('title', __('auth.registration_submitted_title'))

@section('content')
<div class="register-status-card">
    <div class="register-status-icon success"><i class="fa fa-check"></i></div>
    <h1 style="font-size:1.5rem;margin-bottom:0.5rem;">{{ __('auth.application_submitted') }}</h1>
    @include('partials.sweetalert-flash')
    <p class="text-muted">{{ __('auth.registration_success_message') }}</p>
    <div class="register-ref-box">{{ $reference }}</div>
    <p class="text-muted small">{{ __('auth.keep_reference') }}</p>
    <a href="{{ route('church.login') }}" class="btn btn-primary mt-3">{{ __('auth.go_to_sign_in') }}</a>
</div>
@endsection
