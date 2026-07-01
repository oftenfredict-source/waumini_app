@extends('layouts.church')

@section('title', __('pages.branches.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-code-fork',
    'title' => __('pages.branches.title'),
    'subtitle' => __('pages.branches.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.branches')],
    ],
])

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="{{ __('pages.branches.search_placeholder') }}"
                value="{{ $filters['search'] ?? '' }}">
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_statuses') }}</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ __('common.active') }}</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>{{ __('common.inactive') }}</option>
            </select>
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> {{ __('common.search') }}</button>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        @can('create', \App\Models\ChurchBranch::class)
            <a href="{{ route('church.branches.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> {{ __('pages.branches.add_branch') }}
            </a>
        @endcan
    </div>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover table-bordered mb-0">
            <thead>
                <tr>
                    <th>{{ __('common.name') }}</th>
                    <th>{{ __('pages.shared.code') }}</th>
                    <th>{{ __('pages.shared.city') }}</th>
                    <th>{{ __('pages.shared.pastor') }}</th>
                    <th>{{ __('pages.shared.members') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                    <tr>
                        <td>
                            {{ $branch->name }}
                            @if($branch->is_headquarters)
                                <span class="badge badge-primary ml-1">{{ __('pages.shared.headquarters') }}</span>
                            @endif
                        </td>
                        <td><code>{{ $branch->code }}</code></td>
                        <td>{{ $branch->city ?? '—' }}</td>
                        <td>{{ $branch->pastor_name ?? '—' }}</td>
                        <td>{{ $branch->members_count }}</td>
                        <td>
                            <span class="badge badge-{{ $branch->is_active ? 'success' : 'secondary' }}">
                                {{ $branch->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('church.branches.show', $branch) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                            @can('update', $branch)
                                <a href="{{ route('church.branches.edit', $branch) }}" class="btn btn-sm btn-warning"><i class="fa fa-pencil"></i></a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">{{ __('pages.branches.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($branches->hasPages())
        <div class="mt-3">{{ $branches->links() }}</div>
    @endif
</div>
@endsection
