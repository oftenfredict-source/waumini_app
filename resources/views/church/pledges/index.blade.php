@extends('layouts.church')

@section('title', __('pages.pledges.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-handshake-o',
    'title' => __('pages.pledges.title'),
    'subtitle' => __('pages.pledges.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.pledges')],
    ],
])

<div class="row mb-3">
    <div class="col-md-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-bolt fa-3x"></i>
            <div class="info">
                <h4>{{ $stats['active_count'] }}</h4>
                <p>{{ __('pages.shared.active_pledges') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-money fa-3x"></i>
            <div class="info">
                <h4>TZS {{ number_format($stats['total_pledged'], 0) }}</h4>
                <p>{{ __('pages.shared.total_pledged') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-check fa-3x"></i>
            <div class="info">
                <h4>TZS {{ number_format($stats['total_paid'], 0) }}</h4>
                <p>{{ __('pages.shared.total_paid') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-trophy fa-3x"></i>
            <div class="info">
                <h4>{{ $stats['completed_count'] }}</h4>
                <p>{{ __('pages.shared.completed') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-9">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="{{ __('pages.pledges.search_placeholder') }}"
                value="{{ $filters['search'] ?? '' }}">
            <select name="member_id" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_members') }}</option>
                @foreach($members as $member)
                    <option value="{{ $member->id }}" @selected(($filters['member_id'] ?? '') == $member->id)>
                        {{ $member->full_name }}
                    </option>
                @endforeach
            </select>
            <select name="pledge_type" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_types') }}</option>
                @foreach($pledgeTypes as $type)
                    <option value="{{ $type->value }}" @selected(($filters['pledge_type'] ?? '') === $type->value)>
                        {{ $type->label() }}
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
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> {{ __('common.filter') }}</button>
        </form>
    </div>
    <div class="col-md-3 text-md-right">
        @can('create', \App\Models\Pledge::class)
            <a href="{{ route('church.pledges.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> {{ __('pages.pledges.record_pledge') }}
            </a>
        @endcan
        @can('finance.approve')
            <a href="{{ route('church.finance.approvals') }}" class="btn btn-outline-primary mb-2">
                <i class="fa fa-check-circle"></i> {{ __('pages.shared.approvals') }}
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
                        <th>{{ __('common.type') }}</th>
                        <th>{{ __('pages.shared.pledged') }}</th>
                        <th>{{ __('pages.shared.paid') }}</th>
                        <th>{{ __('pages.shared.progress') }}</th>
                        <th>{{ __('pages.shared.due_date') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th width="130">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pledges as $pledge)
                        <tr>
                            <td>
                                {{ $pledge->member?->full_name ?? '—' }}
                                @if($pledge->member?->envelope_number)
                                    <br><small class="text-muted">{{ $pledge->member->envelope_number }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $pledge->pledgeTypeLabel() }}</span>
                            </td>
                            <td><strong>TZS {{ number_format($pledge->pledge_amount, 2) }}</strong></td>
                            <td>TZS {{ number_format($pledge->amount_paid, 2) }}</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: {{ min(100, $pledge->progressPercentage()) }}%">
                                        {{ $pledge->progressPercentage() }}%
                                    </div>
                                </div>
                            </td>
                            <td>{{ $pledge->due_date?->format('M d, Y') ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $pledge->status->badgeClass() }}">
                                    {{ $pledge->status->label() }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    @can('view', $pledge)
                                        <a href="{{ route('church.pledges.show', $pledge) }}" class="btn btn-sm btn-info" title="{{ __('common.view') }}">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update', $pledge)
                                        <a href="{{ route('church.pledges.edit', $pledge) }}" class="btn btn-sm btn-primary" title="{{ __('common.edit') }}">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $pledge)
                                        <form method="POST" action="{{ route('church.pledges.destroy', $pledge) }}" class="d-inline"
                                            data-swal-confirm="Delete this pledge record?"
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
                            <td colspan="8" class="text-center text-muted py-4">
                                {{ __('pages.pledges.empty') }}
                                @can('create', \App\Models\Pledge::class)
                                    <a href="{{ route('church.pledges.create') }}">{{ __('pages.pledges.record_link') }}</a>.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $pledges->links() }}
    </div>
</div>
@endsection
