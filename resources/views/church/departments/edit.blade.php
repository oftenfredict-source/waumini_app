@extends('layouts.church')

@section('title', __('pages.shared.edit_item', ['item' => __('pages.departments.item')]))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-pencil',
    'title' => __('pages.shared.edit_item', ['item' => __('pages.departments.item')]),
    'subtitle' => __('pages.shared.update_subtitle', ['name' => $department->name]),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.departments'), 'route' => 'church.departments.index'],
        ['label' => $department->name],
        ['label' => __('pages.shared.breadcrumb_edit')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.departments.update', $department) }}">
        @csrf
        @method('PUT')
        @include('church.departments._form', ['department' => $department])
        <div class="tile-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.update_item', ['item' => __('pages.departments.item')]) }}</button>
            <a href="{{ route('church.departments.show', $department) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
