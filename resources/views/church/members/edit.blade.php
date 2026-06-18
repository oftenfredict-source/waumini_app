@extends('layouts.church')

@section('title', 'Edit Member')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/member-wizard.css') }}">
@endpush

@section('content')
@php
    $isEdit = true;
    $formAction = route('church.members.update', $member);
    $cancelUrl = route('church.members.show', $member);
    $submitLabel = 'Save Changes';
@endphp

<div class="app-title">
    <div>
        <h1><i class="fa fa-pencil"></i> Edit Member</h1>
        <p>{{ $member->full_name }} — complete the 5-step form to update their profile</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.members.index') }}">Members</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.members.show', $member) }}">{{ $member->full_name }}</a></li>
        <li class="breadcrumb-item">Edit</li>
    </ul>
</div>

@include('church.members._wizard-form')
@endsection

@push('scripts')
<script>
    window.memberWizardConfig = {
        isEdit: true,
        memberId: @json($member->id),
        checkEnvelopeUrl: @json(route('church.members.check-envelope')),
        locationsUrl: @json(asset('data/tanzania-locations.json')),
        csrfToken: @json(csrf_token()),
    };
</script>
<script src="{{ asset('js/member-wizard.js') }}"></script>
@endpush
