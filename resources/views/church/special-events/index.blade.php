@extends('layouts.church')

@section('title', __('pages.special_events.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-star',
    'title' => __('pages.special_events.title'),
    'subtitle' => __('pages.special_events.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.special_events')],
    ],
])

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="{{ __('pages.special_events.search_placeholder') }}"
                value="{{ $filters['search'] ?? '' }}">
            <select name="category" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_categories') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->value }}" @selected(($filters['category'] ?? '') === $category->value)>
                        {{ $category->label() }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_statuses') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="from" class="form-control mr-2 mb-2" value="{{ $filters['from'] ?? '' }}" title="{{ __('common.from') }}">
            <input type="date" name="to" class="form-control mr-2 mb-2" value="{{ $filters['to'] ?? '' }}" title="{{ __('common.to') }}">
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> {{ __('common.search') }}</button>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        @can('create', \App\Models\SpecialEvent::class)
            <a href="{{ route('church.special-events.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> {{ __('pages.special_events.create_event') }}
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
                        <th>{{ __('common.date') }}</th>
                        <th>{{ __('common.title') }}</th>
                        <th>{{ __('common.category') }}</th>
                        <th>{{ __('pages.shared.speaker') }}</th>
                        <th>{{ __('common.venue') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th width="130">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td>{{ $event->event_date->format('M d, Y') }}</td>
                            <td>{{ $event->title }}</td>
                            <td>
                                <span class="badge badge-{{ $event->category->badgeClass() }}">
                                    {{ $event->categoryLabel() }}
                                </span>
                            </td>
                            <td>{{ $event->speaker ?? '—' }}</td>
                            <td>{{ $event->venue ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $event->status->badgeClass() }}">
                                    {{ $event->status->label() }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    @can('view', $event)
                                        <a href="{{ route('church.special-events.show', $event) }}" class="btn btn-sm btn-info" title="{{ __('common.view') }}">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update', $event)
                                        <a href="{{ route('church.special-events.edit', $event) }}" class="btn btn-sm btn-primary" title="{{ __('common.edit') }}">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $event)
                                        <form method="POST" action="{{ route('church.special-events.destroy', $event) }}" class="d-inline"
                                            data-swal-confirm="Delete {{ $event->title }}?"
                                            data-swal-delete
                                            data-swal-confirm-text="{{ __('common.yes_delete') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="{{ __('common.delete') }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                {{ __('pages.special_events.empty') }}
                                @can('create', \App\Models\SpecialEvent::class)
                                    <a href="{{ route('church.special-events.create') }}">{{ __('pages.special_events.create_link') }}</a>.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $events->links() }}
    </div>
</div>
@endsection
