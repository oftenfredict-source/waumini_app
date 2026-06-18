@extends('layouts.church')

@section('title', 'Record Offering')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> Record Offering</h1>
        <p>Submit an offering for approval</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.offerings.index') }}">Offerings</a></li>
        <li class="breadcrumb-item">Record</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.offerings.store') }}">
        @csrf
        @include('church.offerings._form', [
            'members' => $members,
            'services' => $services,
            'paymentMethods' => $paymentMethods,
            'offeringTypes' => $offeringTypes,
            'contributionTypes' => $contributionTypes,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Offering</button>
            <a href="{{ route('church.offerings.index') }}" class="btn btn-secondary">Cancel</a>
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
