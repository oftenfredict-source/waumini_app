@extends('layouts.church')

@section('title', __('members.edit_member'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/member-wizard.css') }}">
@endpush

@section('content')
@php
    $isEdit = true;
    $formAction = route('church.members.update', $member);
    $cancelUrl = route('church.members.show', $member);
    $submitLabel = __('members.save_changes');
@endphp

<div class="app-title">
    <div>
        <h1><i class="fa fa-pencil"></i> {{ __('members.edit_member') }}</h1>
        <p>{{ __('members.edit_subtitle', ['name' => $member->full_name]) }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.members.index') }}">{{ __('menu.members') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.members.show', $member) }}">{{ $member->full_name }}</a></li>
        <li class="breadcrumb-item">{{ __('members.breadcrumb_edit') }}</li>
    </ul>
</div>

@include('church.members._wizard-form')
@endsection

@push('scripts')
@include('partials.member-wizard-i18n')
<script>
    window.memberWizardConfig = {
        isEdit: true,
        memberId: @json($member->id),
        checkEnvelopeUrl: @json(route('church.members.check-envelope')),
        locationsUrl: @json(asset('data/tanzania-locations.json')),
        csrfToken: @json(csrf_token()),
    };
</script>
@include('partials.member-wizard-script')
@endpush
