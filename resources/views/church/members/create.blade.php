@extends('layouts.church')

@section('title', 'Register Member')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/member-wizard.css') }}">
@endpush

@section('content')
@php
    $isEdit = false;
    $formAction = route('church.members.store');
    $cancelUrl = route('church.members.index');
    $submitLabel = 'Save Member';
@endphp

<div class="app-title">
    <div>
        <h1><i class="fa fa-user-plus"></i> Register Member</h1>
        <p>Complete the 5-step registration form</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.members.index') }}">Members</a></li>
        <li class="breadcrumb-item">Register</li>
    </ul>
</div>

@include('church.members._wizard-form')
@endsection

@push('scripts')
<script>
    window.memberWizardConfig = {
        isEdit: false,
        checkEnvelopeUrl: @json(route('church.members.check-envelope')),
        locationsUrl: @json(asset('data/tanzania-locations.json')),
        csrfToken: @json(csrf_token()),
    };
</script>
<script src="{{ asset('js/member-wizard.js') }}"></script>
@endpush
