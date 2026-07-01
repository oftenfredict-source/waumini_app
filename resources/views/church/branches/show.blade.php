@extends('layouts.church')

@section('title', $branch->name)

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-code-fork',
    'title' => $branch->name,
    'subtitle' => $branch->code,
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.branches'), 'route' => 'church.branches.index'],
        ['label' => $branch->code],
    ],
])

<div class="row">
    <div class="col-lg-8">
        <div class="tile mb-3">
            @if($branch->logoUrl())
                <div class="mb-3">
                    <img src="{{ $branch->logoUrl() }}" alt="{{ $branch->name }}" style="max-height:80px;max-width:180px;object-fit:contain;">
                </div>
            @endif
            <table class="table table-borderless table-sm mb-0">
                <tr><th width="180">{{ __('pages.shared.code') }}</th><td><code>{{ $branch->code }}</code></td></tr>
                <tr><th>{{ __('common.type') }}</th><td>{{ $branch->is_headquarters ? __('pages.shared.headquarters') : __('pages.branches.branch_type') }}</td></tr>
                <tr><th>{{ __('pages.shared.pastor') }}</th><td>{{ $branch->pastor_name ?? '—' }}</td></tr>
                <tr><th>{{ __('common.phone') }}</th><td>{{ $branch->phone ?? '—' }}</td></tr>
                <tr><th>{{ __('common.email') }}</th><td>{{ $branch->email ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.city') }}</th><td>{{ $branch->city ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.address') }}</th><td>{{ $branch->address ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.members') }}</th><td>{{ $branch->members_count }}</td></tr>
                <tr><th>{{ __('pages.shared.leaders') }}</th><td>{{ $branch->leaders_count }}</td></tr>
                <tr><th>{{ __('common.status') }}</th><td>
                    <span class="badge badge-{{ $branch->is_active ? 'success' : 'secondary' }}">
                        {{ $branch->is_active ? __('common.active') : __('common.inactive') }}
                    </span>
                </td></tr>
            </table>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="tile">
            @can('update', $branch)
                <a href="{{ route('church.branches.edit', $branch) }}" class="btn btn-warning btn-block mb-2">
                    <i class="fa fa-pencil"></i> {{ __('pages.shared.edit_item', ['item' => __('pages.branches.item')]) }}
                </a>
            @endcan
            <a href="{{ route('church.members.index', ['branch_id' => $branch->id]) }}" class="btn btn-info btn-block">
                <i class="fa fa-users"></i> {{ __('pages.branches.view_members') }}
            </a>
        </div>
    </div>
</div>
@endsection
