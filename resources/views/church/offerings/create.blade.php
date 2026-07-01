@extends('layouts.church')

@section('title', __('pages.offerings.record_offering'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-plus',
    'title' => __('pages.offerings.record_offering'),
    'subtitle' => __('pages.offerings.create_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.offerings'), 'route' => 'church.offerings.index'],
        ['label' => __('pages.shared.breadcrumb_record')],
    ],
])

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
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.save_item', ['item' => __('pages.offerings.item')]) }}</button>
            <a href="{{ route('church.offerings.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
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
