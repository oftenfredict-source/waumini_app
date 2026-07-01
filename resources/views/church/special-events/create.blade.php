@extends('layouts.church')

@section('title', __('pages.special_events.create_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-plus',
    'title' => __('pages.special_events.create_title'),
    'subtitle' => __('pages.special_events.create_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.special_events'), 'route' => 'church.special-events.index'],
        ['label' => __('pages.shared.breadcrumb_create')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.special-events.store') }}">
        @csrf
        @include('church.special-events._form', [
            'categories' => $categories,
            'statuses' => $statuses,
        ])
        <div class="tile-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.save_item', ['item' => __('pages.special_events.item')]) }}</button>
            <a href="{{ route('church.special-events.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@include('church.special-events._form-scripts')
@endpush
