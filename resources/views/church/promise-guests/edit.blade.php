@extends('layouts.church')

@section('title', __('pages.promise_guests.edit_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-pencil',
    'title' => __('pages.promise_guests.edit_title'),
    'subtitle' => $guest->name,
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('pages.promise_guests.breadcrumb'), 'route' => 'church.promise-guests.index'],
        ['label' => __('pages.shared.breadcrumb_edit')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.promise-guests.update', $guest) }}">
        @csrf
        @method('PUT')
        @include('church.promise-guests._form', ['guest' => $guest])
        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.update_item', ['item' => __('pages.promise_guests.item')]) }}</button>
            <a href="{{ route('church.promise-guests.show', $guest) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
