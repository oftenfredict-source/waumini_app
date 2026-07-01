@extends('layouts.church')

@section('title', __('pages.shared.edit_item', ['item' => __('pages.tithes.item')]))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-pencil',
    'title' => __('pages.shared.edit_item', ['item' => __('pages.tithes.item')]),
    'subtitle' => $tithe->member?->full_name ?? __('pages.tithes.record_fallback'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.tithes'), 'route' => 'church.tithes.index'],
        ['label' => __('pages.shared.breadcrumb_edit')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.tithes.update', $tithe) }}">
        @csrf
        @method('PUT')
        @include('church.tithes._form', [
            'tithe' => $tithe,
            'members' => $members,
            'paymentMethods' => $paymentMethods,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.update_item', ['item' => __('pages.tithes.item')]) }}</button>
            <a href="{{ route('church.tithes.show', $tithe) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
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
