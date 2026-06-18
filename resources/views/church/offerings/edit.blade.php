@extends('layouts.church')

@section('title', 'Edit Offering')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-pencil"></i> Edit Offering</h1>
        <p>{{ $offering->contributorLabel() }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.offerings.index') }}">Offerings</a></li>
        <li class="breadcrumb-item">Edit</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.offerings.update', $offering) }}">
        @csrf
        @method('PUT')
        @include('church.offerings._form', [
            'offering' => $offering,
            'members' => $members,
            'services' => $services,
            'paymentMethods' => $paymentMethods,
            'offeringTypes' => $offeringTypes,
            'contributionTypes' => $contributionTypes,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Offering</button>
            <a href="{{ route('church.offerings.show', $offering) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@include('church.offerings._form-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const paymentSelect = document.getElementById('payment_method');
    const referenceGroup = document.getElementById('referenceGroup');
    if (!paymentSelect || !referenceGroup) return;

    function toggleReference() {
        const needsRef = ['bank_transfer', 'mobile_money', 'cheque'].includes(paymentSelect.value);
        referenceGroup.style.display = needsRef ? '' : 'none';
    }

    paymentSelect.addEventListener('change', toggleReference);
    toggleReference();
});
</script>
@endpush
