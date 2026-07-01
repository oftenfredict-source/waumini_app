@extends('layouts.church')

@section('title', __('pages.shared.edit_item', ['item' => __('pages.system_users.item')]))

@section('content')
@include('church.system.partials.nav')

@include('partials.page-header', [
    'icon' => 'fa fa-edit',
    'title' => __('pages.shared.edit_item', ['item' => __('pages.system_users.item')]),
    'subtitle' => $user->name,
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.manage_users'), 'route' => 'church.system.users.index'],
        ['label' => __('pages.shared.breadcrumb_edit')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.system.users.update', $user) }}">
        @csrf
        @method('PUT')
        @include('church.system.users._form', ['user' => $user])
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.save_changes') }}</button>
        <a href="{{ route('church.system.users.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
    </form>
</div>
@endsection
