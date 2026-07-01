@extends('layouts.church')

@section('title', __('pages.celebrations.edit_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-pencil',
    'title' => __('pages.celebrations.edit_title'),
    'subtitle' => $celebration->title,
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.celebrations'), 'route' => 'church.celebrations.index'],
        ['label' => __('pages.shared.breadcrumb_edit')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.celebrations.update', $celebration) }}">
        @csrf
        @method('PUT')
        @include('church.celebrations._form', ['celebration' => $celebration])
        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.save_changes') }}</button>
            <a href="{{ route('church.celebrations.show', $celebration) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
