@extends('layouts.church-register')

@section('title', __('auth.member_registration'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/member-wizard.css') }}">
@endpush

@section('content')
@php
    $isEdit = false;
    $isSelfRegistration = true;
    $formAction = route('church.register.submit', ['church' => $church->slug]);
    $cancelUrl = route('church.login');
    $submitLabel = __('auth.submit_application');
@endphp

<div class="register-hero">
    <h1>{{ __('auth.member_registration') }}</h1>
    <p>{{ __('auth.registration_hero') }}</p>
</div>

@include('partials.sweetalert-flash')

<div class="register-progress-box">
    <div class="register-progress-meta">
        <span id="registerProgressLabel">{{ __('register.step_progress', ['current' => 1, 'total' => 5]) }}</span>
        <span id="registerProgressStepName">{{ __('register.steps.personal') }}</span>
    </div>
    <div class="register-progress-track">
        <div class="register-progress-fill" id="registerProgressFill"></div>
    </div>
</div>

<div class="register-form-card">
    @include('church.members._wizard-form')
</div>

<p class="register-form-footer">
    {{ __('auth.already_have_account') }} <a href="{{ route('church.login') }}">{{ __('auth.sign_in_link') }}</a>
</p>
@endsection

@push('scripts')
@include('partials.member-wizard-i18n')
<script>
    window.memberWizardConfig = {
        isEdit: false,
        isSelfRegistration: true,
        checkEnvelopeUrl: null,
        locationsUrl: @json(asset('data/tanzania-locations.json')),
        csrfToken: @json(csrf_token()),
    };

    window.registerStepProgress = {
        template: @json(__('register.step_progress')),
        total: 5,
    };

    window.registerStepNames = @json([
        __('register.steps.personal'),
        __('register.steps.contact'),
        __('register.steps.residence'),
        __('register.steps.family'),
        __('register.steps.review'),
    ]);
</script>
@include('partials.member-wizard-script')
@endpush
