@extends('layouts.church')

@section('title', __('pages.celebrations.create_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-plus',
    'title' => __('pages.celebrations.create_title'),
    'subtitle' => __('pages.celebrations.create_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.celebrations'), 'route' => 'church.celebrations.index'],
        ['label' => __('pages.shared.breadcrumb_add')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.celebrations.store') }}">
        @csrf
        @include('church.celebrations._form')
        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.save_item', ['item' => __('pages.celebrations.item')]) }}</button>
            <a href="{{ route('church.celebrations.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
