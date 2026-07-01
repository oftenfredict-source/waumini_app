@extends('layouts.church')

@section('title', __('pages.shared.edit_item', ['item' => __('pages.offerings.item')]))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-pencil',
    'title' => __('pages.shared.edit_item', ['item' => __('pages.offerings.item')]),
    'subtitle' => $offering->contributorLabel(),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.offerings'), 'route' => 'church.offerings.index'],
        ['label' => __('pages.shared.breadcrumb_edit')],
    ],
])

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
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.update_item', ['item' => __('pages.offerings.item')]) }}</button>
            <a href="{{ route('church.offerings.show', $offering) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
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
