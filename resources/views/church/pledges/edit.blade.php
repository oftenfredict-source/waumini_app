@extends('layouts.church')

@section('title', __('pages.shared.edit_item', ['item' => __('pages.pledges.item')]))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-pencil',
    'title' => __('pages.shared.edit_item', ['item' => __('pages.pledges.item')]),
    'subtitle' => $pledge->member?->full_name ?? __('pages.pledges.record_fallback'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.pledges'), 'route' => 'church.pledges.index'],
        ['label' => __('pages.shared.breadcrumb_edit')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.pledges.update', $pledge) }}">
        @csrf
        @method('PUT')
        @include('church.pledges._form', [
            'pledge' => $pledge,
            'members' => $members,
            'pledgeTypes' => $pledgeTypes,
            'frequencies' => $frequencies,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.update_item', ['item' => __('pages.pledges.item')]) }}</button>
            <a href="{{ route('church.pledges.show', $pledge) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@include('church.pledges._form-scripts')
@endpush
