@extends('layouts.church')

@section('title', __('pages.promise_guests.create_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-plus',
    'title' => __('pages.promise_guests.create_title'),
    'subtitle' => __('pages.promise_guests.create_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('pages.promise_guests.breadcrumb'), 'route' => 'church.promise-guests.index'],
        ['label' => __('pages.shared.breadcrumb_add')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.promise-guests.store') }}">
        @csrf
        @include('church.promise-guests._form')
        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.save_item', ['item' => __('pages.promise_guests.item')]) }}</button>
            <a href="{{ route('church.promise-guests.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
