@extends('layouts.church')

@section('title', __('pages.special_events.edit_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-pencil',
    'title' => __('pages.special_events.edit_title'),
    'subtitle' => $event->title,
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.special_events'), 'route' => 'church.special-events.index'],
        ['label' => __('pages.shared.breadcrumb_edit')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.special-events.update', $event) }}">
        @csrf
        @method('PUT')
        @include('church.special-events._form', [
            'event' => $event,
            'categories' => $categories,
            'statuses' => $statuses,
        ])
        <div class="tile-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.update_item', ['item' => __('pages.special_events.item')]) }}</button>
            <a href="{{ route('church.special-events.show', $event) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@include('church.special-events._form-scripts')
@endpush
