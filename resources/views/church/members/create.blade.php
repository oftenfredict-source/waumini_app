@extends('layouts.church')

@section('title', __('members.register_member'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/member-wizard.css') }}">
@endpush

@section('content')
@php
    $isEdit = false;
    $formAction = route('church.members.store');
    $cancelUrl = route('church.members.index');
    $submitLabel = __('members.save_member');
@endphp

<div class="app-title">
    <div>
        <h1><i class="fa fa-user-plus"></i> {{ __('members.register_member') }}</h1>
        <p>{{ __('members.register_subtitle') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">{{ __('menu.dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.members.index') }}">{{ __('menu.members') }}</a></li>
        <li class="breadcrumb-item">{{ __('members.breadcrumb_register') }}</li>
    </ul>
</div>

@include('partials.member-registration-link')

@include('church.members._wizard-form')
@endsection

@push('scripts')
@include('partials.member-wizard-i18n')
<script>
    window.memberWizardConfig = {
        isEdit: false,
        checkEnvelopeUrl: @json(route('church.members.check-envelope')),
        locationsUrl: @json(asset('data/tanzania-locations.json')),
        csrfToken: @json(csrf_token()),
    };
</script>
@include('partials.member-wizard-script')
@endpush
