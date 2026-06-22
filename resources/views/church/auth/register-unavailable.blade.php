@extends('layouts.church-register')

@section('title', 'Registration Unavailable')

@section('content')
<div class="register-status-card">
    <div class="register-status-icon warning"><i class="fa fa-exclamation-triangle"></i></div>
    <h1 style="font-size:1.4rem;margin-bottom:0.5rem;">Registration unavailable</h1>
    <p class="text-muted">We could not identify your church from this link. Please use the registration link provided by your church office.</p>
    <a href="{{ route('church.login') }}" class="btn btn-primary mt-3">Back to sign in</a>
</div>
@endsection
