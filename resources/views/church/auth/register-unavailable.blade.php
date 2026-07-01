@extends('layouts.church-register')

@section('title', __('auth.registration_unavailable_title'))

@section('content')
<div class="register-status-card">
    <div class="register-status-icon warning"><i class="fa fa-exclamation-triangle"></i></div>
    <h1 style="font-size:1.4rem;margin-bottom:0.5rem;">{{ __('auth.registration_unavailable_heading') }}</h1>
    <p class="text-muted">{{ __('auth.registration_unavailable_message') }}</p>
    <a href="{{ route('church.login') }}" class="btn btn-primary mt-3">{{ __('auth.back_to_sign_in') }}</a>
</div>
@endsection
