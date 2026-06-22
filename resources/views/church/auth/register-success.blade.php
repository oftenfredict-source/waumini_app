@extends('layouts.church-register')

@section('title', 'Registration Submitted')

@section('content')
<div class="register-status-card">
    <div class="register-status-icon success"><i class="fa fa-check"></i></div>
    <h1 style="font-size:1.5rem;margin-bottom:0.5rem;">Application submitted</h1>
    @include('partials.sweetalert-flash')
    <p class="text-muted">Thank you. Your church will review your application and contact you with your member ID and password.</p>
    <div class="register-ref-box">{{ $reference }}</div>
    <p class="text-muted small">Keep this reference number for your records.</p>
    <a href="{{ route('church.login') }}" class="btn btn-primary mt-3">Go to sign in</a>
</div>
@endsection
