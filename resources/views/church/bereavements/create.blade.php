@extends('layouts.church')

@section('title', __('pages.bereavements.create_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-plus',
    'title' => __('pages.bereavements.create_title'),
    'subtitle' => __('pages.bereavements.create_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.bereavements'), 'route' => 'church.bereavements.index'],
        ['label' => __('pages.shared.breadcrumb_create')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.bereavements.store') }}">
        @csrf
        @include('church.bereavements._form', ['members' => $members])
        <div class="tile-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.save_item', ['item' => __('pages.bereavements.item')]) }}</button>
            <a href="{{ route('church.bereavements.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('affected_member_id');
    const nameInput = document.querySelector('input[name="deceased_name"]');
    if (!select || !nameInput) return;

    select.addEventListener('change', function () {
        const option = select.options[select.selectedIndex];
        if (option.value && !nameInput.value.trim()) {
            nameInput.value = option.dataset.name || '';
        }
    });
});
</script>
@endpush
