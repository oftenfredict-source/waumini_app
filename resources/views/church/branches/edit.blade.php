@extends('layouts.church')

@section('title', __('pages.shared.edit_item', ['item' => __('pages.branches.item')]))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-pencil',
    'title' => __('pages.shared.edit_item', ['item' => __('pages.branches.item')]),
    'subtitle' => $branch->name,
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.branches'), 'route' => 'church.branches.index'],
        ['label' => $branch->code],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.branches.update', $branch) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('church.branches.partials.form', ['branch' => $branch])
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.save_changes') }}</button>
        <a href="{{ route('church.branches.show', $branch) }}" class="btn btn-secondary">{{ __('common.back') }}</a>
    </form>
</div>
@endsection
