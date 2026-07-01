@extends('layouts.church')

@section('title', __('pages.announcements.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-bullhorn',
    'title' => __('pages.announcements.title'),
    'subtitle' => __('pages.announcements.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.announcements')],
    ],
])

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="{{ __('pages.announcements.search_placeholder') }}"
                value="{{ $filters['search'] ?? '' }}">
            <select name="type" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_types') }}</option>
                @foreach($types as $type)
                    <option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>{{ $type->label() }}</option>
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
        @can('create', \App\Models\Announcement::class)
            <a href="{{ route('church.announcements.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> {{ __('pages.announcements.create_announcement') }}
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
                        <th>{{ __('common.title') }}</th>
                        <th>{{ __('common.type') }}</th>
                        <th>{{ __('pages.shared.audience') }}</th>
                        <th>{{ __('pages.shared.period') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th>{{ __('common.created') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                        <tr>
                            <td>
                                @if($announcement->is_pinned)
                                    <i class="fa fa-thumb-tack text-warning" title="{{ __('pages.shared.pinned') }}"></i>
                                @endif
                                {{ $announcement->title }}
                            </td>
                            <td>
                                <span class="badge badge-{{ $announcement->type->badgeClass() }}">
                                    {{ $announcement->type->label() }}
                                </span>
                            </td>
                            <td>{{ $announcement->target_type->label() }}</td>
                            <td>
                                {{ $announcement->start_date?->format('M d, Y') ?? __('pages.shared.now') }}
                                —
                                {{ $announcement->end_date?->format('M d, Y') ?? __('pages.shared.open') }}
                            </td>
                            <td>
                                <span class="badge badge-{{ $announcement->isCurrentlyActive() ? 'success' : 'secondary' }}">
                                    {{ $announcement->isCurrentlyActive() ? __('common.active') : __('common.inactive') }}
                                </span>
                            </td>
                            <td>{{ $announcement->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('church.announcements.show', $announcement) }}" class="btn btn-sm btn-info">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                {{ __('pages.announcements.empty') }}
                                @can('create', \App\Models\Announcement::class)
                                    <a href="{{ route('church.announcements.create') }}">{{ __('pages.shared.create_one') }}</a>.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $announcements->links() }}
    </div>
</div>
@endsection
