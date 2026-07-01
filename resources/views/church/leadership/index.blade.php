@extends('layouts.church')

@section('title', __('pages.leadership.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-star',
    'title' => __('pages.leadership.title'),
    'subtitle' => __('pages.leadership.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.leadership')],
    ],
])

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="{{ __('pages.leadership.search_placeholder') }}"
                value="{{ $filters['search'] ?? '' }}">
            <select name="position" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_positions') }}</option>
                @foreach($positions as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['position'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_statuses') }}</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ __('common.active') }}</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>{{ __('common.inactive') }}</option>
            </select>
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> {{ __('common.filter') }}</button>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        @can('create', \App\Models\Leader::class)
            <a href="{{ route('church.leadership.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> {{ __('pages.leadership.assign_leadership') }}
            </a>
        @endcan
    </div>
</div>

<div class="tile">
    <div class="tile-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>{{ __('common.member') }}</th>
                        <th>{{ __('pages.shared.member_id') }}</th>
                        <th>{{ __('pages.shared.position') }}</th>
                        <th>{{ __('pages.shared.appointed') }}</th>
                        <th>{{ __('pages.shared.end_date') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaders as $leader)
                        <tr>
                            <td>{{ $leader->member?->full_name ?? '—' }}</td>
                            <td><code>{{ $leader->member?->member_number ?? '—' }}</code></td>
                            <td>{{ $leader->positionLabel() }}</td>
                            <td>{{ $leader->appointment_date->format('M d, Y') }}</td>
                            <td>{{ $leader->end_date?->format('M d, Y') ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $leader->isCurrentlyActive() ? 'success' : 'secondary' }}">
                                    {{ $leader->isCurrentlyActive() ? __('common.active') : __('common.inactive') }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('church.leadership.show', $leader) }}" class="btn btn-sm btn-info" title="{{ __('common.view') }}">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                {{ __('pages.leadership.empty') }}
                                @can('create', \App\Models\Leader::class)
                                    <a href="{{ route('church.leadership.create') }}">{{ __('pages.leadership.assign_link') }}</a>.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $leaders->links() }}
    </div>
</div>
@endsection
