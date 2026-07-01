@extends('layouts.church')

@section('title', __('pages.branches.add_branch'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-plus',
    'title' => __('pages.branches.add_branch'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.branches'), 'route' => 'church.branches.index'],
        ['label' => __('pages.shared.breadcrumb_add')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.branches.store') }}" enctype="multipart/form-data">
        @csrf
        @include('church.branches.partials.form')
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.save_item', ['item' => __('pages.branches.item')]) }}</button>
        <a href="{{ route('church.branches.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
    </form>
</div>
@endsection
