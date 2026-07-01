@extends('layouts.church')

@section('title', __('pages.tithes.title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-money',
    'title' => __('pages.tithes.title'),
    'subtitle' => __('pages.tithes.subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.tithes')],
    ],
])

<div class="row mb-3">
    <div class="col-md-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-check fa-3x"></i>
            <div class="info">
                <h4>TZS {{ number_format($stats['month_approved'], 0) }}</h4>
                <p>{{ __('pages.shared.approved_this_month') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-university fa-3x"></i>
            <div class="info">
                <h4>TZS {{ number_format($stats['total_approved'], 0) }}</h4>
                <p>{{ __('pages.shared.total_approved') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-clock-o fa-3x"></i>
            <div class="info">
                <h4>{{ $stats['pending_count'] }}</h4>
                <p>{{ __('pages.shared.pending_approval') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small danger coloured-icon">
            <i class="icon fa fa-hourglass-half fa-3x"></i>
            <div class="info">
                <h4>TZS {{ number_format($stats['pending_amount'], 0) }}</h4>
                <p>{{ __('pages.shared.pending_amount') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-9">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="{{ __('pages.tithes.search_placeholder') }}"
                value="{{ $filters['search'] ?? '' }}">
            <select name="member_id" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_members') }}</option>
                @foreach($members as $member)
                    <option value="{{ $member->id }}" @selected(($filters['member_id'] ?? '') == $member->id)>
                        {{ $member->full_name }}
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
            <select name="payment_method" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_methods') }}</option>
                @foreach($paymentMethods as $method)
                    <option value="{{ $method->value }}" @selected(($filters['payment_method'] ?? '') === $method->value)>
                        {{ $method->label() }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="from" class="form-control mr-2 mb-2" value="{{ $filters['from'] ?? '' }}" title="{{ __('common.from') }}">
            <input type="date" name="to" class="form-control mr-2 mb-2" value="{{ $filters['to'] ?? '' }}" title="{{ __('common.to') }}">
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> {{ __('common.filter') }}</button>
        </form>
    </div>
    <div class="col-md-3 text-md-right">
        @can('create', \App\Models\Tithe::class)
            <a href="{{ route('church.tithes.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> {{ __('pages.tithes.record_tithe') }}
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
                        <th>{{ __('common.amount') }}</th>
                        <th>{{ __('common.date') }}</th>
                        <th>{{ __('pages.shared.payment') }}</th>
                        <th>{{ __('common.reference') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th>{{ __('pages.shared.recorded_by') }}</th>
                        <th width="130">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tithes as $tithe)
                        <tr>
                            <td>
                                {{ $tithe->member?->full_name ?? '—' }}
                                @if($tithe->member?->envelope_number)
                                    <br><small class="text-muted">{{ $tithe->member->envelope_number }}</small>
                                @endif
                            </td>
                            <td><strong>TZS {{ number_format($tithe->amount, 2) }}</strong></td>
                            <td>{{ $tithe->tithe_date->format('M d, Y') }}</td>
                            <td>{{ $tithe->payment_method?->label() ?? '—' }}</td>
                            <td>{{ $tithe->reference_number ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $tithe->approval_status->badgeClass() }}">
                                    {{ $tithe->approval_status->label() }}
                                </span>
                            </td>
                            <td>{{ $tithe->recorder?->name ?? '—' }}</td>
                            <td>
                                <div class="btn-group">
                                    @can('view', $tithe)
                                        <a href="{{ route('church.tithes.show', $tithe) }}" class="btn btn-sm btn-info" title="{{ __('common.view') }}">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update', $tithe)
                                        <a href="{{ route('church.tithes.edit', $tithe) }}" class="btn btn-sm btn-primary" title="{{ __('common.edit') }}">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $tithe)
                                        <form method="POST" action="{{ route('church.tithes.destroy', $tithe) }}" class="d-inline"
                                            data-swal-confirm="Delete this tithe record?"
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
                                {{ __('pages.tithes.empty') }}
                                @can('create', \App\Models\Tithe::class)
                                    <a href="{{ route('church.tithes.create') }}">{{ __('pages.tithes.record_link') }}</a>.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $tithes->links() }}
    </div>
</div>
@endsection
