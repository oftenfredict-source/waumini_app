@extends('layouts.church-register')

@section('title', 'Register as Member')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/member-wizard.css') }}">
@endpush

@section('content')
@php
    $isEdit = false;
    $isSelfRegistration = true;
    $formAction = route('church.register.submit', ['church' => $church->slug]);
    $cancelUrl = route('church.login');
    $submitLabel = 'Submit Application';
@endphp

<div class="register-hero">
    <h1>Member Registration</h1>
    <p>Fill in your details below. Your church leadership will review your application and send you login credentials after approval.</p>
</div>

@include('partials.sweetalert-flash')

<div class="register-progress-box">
    <div class="register-progress-meta">
        <span>Step <strong id="registerProgressLabel">1</strong> of 5</span>
        <span id="registerProgressStepName">Personal Information</span>
    </div>
    <div class="register-progress-track">
        <div class="register-progress-fill" id="registerProgressFill"></div>
    </div>
</div>

<div class="register-form-card">
    @include('church.members._wizard-form')
</div>

<p class="register-form-footer">
    Already have an account? <a href="{{ route('church.login') }}">Sign in</a>
</p>
@endsection

@push('scripts')
<script>
    window.memberWizardConfig = {
        isEdit: false,
        isSelfRegistration: true,
        checkEnvelopeUrl: null,
        locationsUrl: @json(asset('data/tanzania-locations.json')),
        csrfToken: @json(csrf_token()),
    };

    window.registerStepNames = [
        'Personal Information',
        'Contact & Origin',
        'Residence',
        'Family Information',
        'Review & Submit'
    ];
</script>
@include('partials.member-wizard-script')
@endpush
