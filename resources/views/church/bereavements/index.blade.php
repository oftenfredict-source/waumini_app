@extends('layouts.church')

@section('title', __('pages.bereavements.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-heart',
    'title' => __('pages.bereavements.title'),
    'subtitle' => __('pages.bereavements.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.bereavements')],
    ],
])

<div class="row mb-3">
    <div class="col-md-8">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="{{ __('pages.bereavements.search_placeholder') }}"
                value="{{ $filters['search'] ?? '' }}">
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
        @can('create', \App\Models\BereavementEvent::class)
            <a href="{{ route('church.bereavements.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> {{ __('pages.bereavements.create_bereavement') }}
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
                        <th>{{ __('pages.shared.incident_date') }}</th>
                        <th>{{ __('pages.shared.deceased_affected') }}</th>
                        <th>{{ __('pages.shared.contribution_period') }}</th>
                        <th>{{ __('pages.shared.total_raised') }}</th>
                        <th>{{ __('pages.shared.contributors') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th width="130">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td>{{ $event->incident_date->format('M d, Y') }}</td>
                            <td>
                                {{ $event->deceased_name }}
                                @if($event->affectedMember)
                                    <br><small class="text-muted">{{ __('pages.bereavements.member_label', ['name' => $event->affectedMember->full_name]) }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $event->contribution_start_date->format('M d') }}
                                – {{ $event->contribution_end_date->format('M d, Y') }}
                            </td>
                            <td>TZS {{ number_format($event->total_raised ?? 0, 2) }}</td>
                            <td>{{ $event->contributors_count ?? 0 }}</td>
                            <td>
                                <span class="badge badge-{{ $event->status->badgeClass() }}">
                                    {{ $event->status->label() }}
                                </span>
                                @if($event->isExpired())
                                    <span class="badge badge-warning">{{ __('common.expired') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    @can('view', $event)
                                        <a href="{{ route('church.bereavements.show', $event) }}" class="btn btn-sm btn-info" title="{{ __('common.view') }}">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update', $event)
                                        <a href="{{ route('church.bereavements.edit', $event) }}" class="btn btn-sm btn-primary" title="{{ __('common.edit') }}">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $event)
                                        <form method="POST" action="{{ route('church.bereavements.destroy', $event) }}" class="d-inline"
                                            data-swal-confirm="Delete bereavement for {{ $event->deceased_name }}?"
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
                                {{ __('pages.bereavements.empty') }}
                                @can('create', \App\Models\BereavementEvent::class)
                                    <a href="{{ route('church.bereavements.create') }}">{{ __('pages.bereavements.create_link') }}</a>.
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
