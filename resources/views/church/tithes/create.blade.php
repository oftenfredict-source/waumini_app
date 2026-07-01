@extends('layouts.church')

@section('title', __('pages.tithes.record_tithe'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-plus',
    'title' => __('pages.tithes.record_tithe'),
    'subtitle' => __('pages.tithes.create_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.tithes'), 'route' => 'church.tithes.index'],
        ['label' => __('pages.shared.breadcrumb_record')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.tithes.store') }}">
        @csrf
        @include('church.tithes._form', [
            'members' => $members,
            'paymentMethods' => $paymentMethods,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.save_item', ['item' => __('pages.tithes.item')]) }}</button>
            <a href="{{ route('church.tithes.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
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
