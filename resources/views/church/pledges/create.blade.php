@extends('layouts.church')

@section('title', __('pages.pledges.record_pledge'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-plus',
    'title' => __('pages.pledges.record_pledge'),
    'subtitle' => __('pages.pledges.create_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.pledges'), 'route' => 'church.pledges.index'],
        ['label' => __('pages.shared.breadcrumb_record')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.pledges.store') }}">
        @csrf
        @include('church.pledges._form', [
            'members' => $members,
            'pledgeTypes' => $pledgeTypes,
            'frequencies' => $frequencies,
        ])
        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.save_item', ['item' => __('pages.pledges.item')]) }}</button>
            <a href="{{ route('church.pledges.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@include('church.pledges._form-scripts')
@endpush
