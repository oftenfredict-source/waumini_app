@extends('layouts.church')

@section('title', __('pages.system_users.add_staff'))

@section('content')
@include('church.system.partials.nav')

@include('partials.page-header', [
    'icon' => 'fa fa-user-plus',
    'title' => __('pages.system_users.add_staff'),
    'subtitle' => __('pages.system_users.create_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.manage_users'), 'route' => 'church.system.users.index'],
        ['label' => __('pages.shared.breadcrumb_add')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.system.users.store') }}">
        @csrf
        @include('church.system.users._form')
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.system_users.create_user_button') }}</button>
        <a href="{{ route('church.system.users.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
    </form>
</div>
@endsection
