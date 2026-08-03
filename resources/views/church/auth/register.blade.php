@extends('layouts.church-register')

@section('title', __('auth.member_registration'))

@push('styles')
@php
    $wizardCssVersion = @filemtime(public_path('css/member-wizard.css')) ?: time();
@endphp
<link rel="stylesheet" href="{{ \App\Support\WauminiBrand::publicAsset('css/member-wizard.css') }}?v={{ $wizardCssVersion }}">
@endpush

@section('content')
@php
    $isEdit = false;
    $isSelfRegistration = true;
    $formAction = route('church.register.submit', array_filter([
        'church' => $church->slug,
        'branch' => $lockedBranch?->code,
    ]));
    $cancelUrl = route('church.login');
    $submitLabel = __('auth.submit_application');
@endphp

<div class="register-hero">
    <h1>{{ __('auth.member_registration') }}</h1>
    <p>{{ __('auth.registration_hero') }}</p>
    @if(!empty($branchLocked) && $lockedBranch)
        <p class="mb-0 mt-2"><strong>{{ $lockedBranch->displayLabel() }}</strong></p>
    @endif
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
@php
    $registerStepNames = [
        __('register.steps.personal'),
        __('register.steps.contact'),
        __('register.steps.residence'),
        __('register.steps.family'),
        __('register.steps.review'),
    ];
@endphp
<script>
    @php
        $wizardSettings = app(\App\Services\Church\ChurchSettingsService::class);
    @endphp
    window.memberWizardConfig = {
        isEdit: false,
        isSelfRegistration: true,
        kipaimaraRegistrationEnabled: @json((bool) $wizardSettings->get($church, 'kipaimara_registration_enabled', false)),
        kipaimaraMinAge: @json($wizardSettings->kipaimaraMinAge($church)),
        checkEnvelopeUrl: null,
        locationsUrl: @json(asset('data/tanzania-locations.json')),
        csrfToken: @json(csrf_token()),
        labels: {
            secondary_family_hint: @json(__('members.fields.secondary_family_hint')),
        },
    };

    window.registerStepProgress = {
        template: @json(__('register.step_progress')),
        total: 5,
    };

    window.registerStepNames = @json($registerStepNames);
</script>
@include('partials.member-wizard-script')
@endpush
