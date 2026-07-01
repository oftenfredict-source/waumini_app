@extends('layouts.church')

@section('title', __('pages.assets.create_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-plus',
    'title' => __('pages.assets.create_title'),
    'subtitle' => __('pages.assets.create_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('pages.assets.breadcrumb'), 'route' => 'church.assets.index'],
        ['label' => __('pages.shared.breadcrumb_record')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.assets.store') }}" enctype="multipart/form-data">
        @csrf
        @include('church.assets._form')
        <div class="tile-footer mt-4">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.save_item', ['item' => __('pages.assets.item')]) }}</button>
            <a href="{{ route('church.assets.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
