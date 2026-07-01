@extends('layouts.church')

@section('title', __('pages.bereavements.edit_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-pencil',
    'title' => __('pages.bereavements.edit_title'),
    'subtitle' => $event->deceased_name,
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.bereavements'), 'route' => 'church.bereavements.index'],
        ['label' => __('pages.shared.breadcrumb_edit')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.bereavements.update', $event) }}">
        @csrf
        @method('PUT')
        @include('church.bereavements._form', [
            'event' => $event,
            'members' => $members,
        ])
        <div class="tile-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.update_item', ['item' => __('pages.bereavements.item')]) }}</button>
            <a href="{{ route('church.bereavements.show', $event) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
