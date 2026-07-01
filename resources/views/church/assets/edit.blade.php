@extends('layouts.church')

@section('title', __('pages.assets.edit_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-pencil',
    'title' => __('pages.assets.edit_title'),
    'subtitle' => $asset->name . ' — ' . $asset->asset_tag,
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('pages.assets.breadcrumb'), 'route' => 'church.assets.index'],
        ['label' => $asset->name],
        ['label' => __('pages.shared.breadcrumb_edit')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.assets.update', $asset) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('church.assets._form')
        <div class="tile-footer mt-4">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.save_changes') }}</button>
            <a href="{{ route('church.assets.show', $asset) }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
