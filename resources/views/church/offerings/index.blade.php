@extends('layouts.church')

@section('title', 'Offerings')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-gift"></i> Offerings</h1>
        <p>Record and track church offerings and special gifts</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Offerings</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-check fa-3x"></i>
            <div class="info">
                <h4>TZS {{ number_format($stats['month_approved'], 0) }}</h4>
                <p>Approved This Month</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-university fa-3x"></i>
            <div class="info">
                <h4>TZS {{ number_format($stats['total_approved'], 0) }}</h4>
                <p>Total Approved</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-clock-o fa-3x"></i>
            <div class="info">
                <h4>{{ $stats['pending_count'] }}</h4>
                <p>Pending Approval</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small danger coloured-icon">
            <i class="icon fa fa-hourglass-half fa-3x"></i>
            <div class="info">
                <h4>TZS {{ number_format($stats['pending_amount'], 0) }}</h4>
                <p>Pending Amount</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-9">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search member or notes..."
                value="{{ $filters['search'] ?? '' }}">
            <select name="member_id" class="form-control mr-2 mb-2">
                <option value="">All members</option>
                @foreach($members as $member)
                    <option value="{{ $member->id }}" @selected(($filters['member_id'] ?? '') == $member->id)>
                        {{ $member->full_name }}
                    </option>
                @endforeach
            </select>
            <select name="offering_type" class="form-control mr-2 mb-2">
                <option value="">All types</option>
                @foreach($offeringTypes as $type)
                    <option value="{{ $type->value }}" @selected(($filters['offering_type'] ?? '') === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            <select name="payment_method" class="form-control mr-2 mb-2">
                <option value="">All methods</option>
                @foreach($paymentMethods as $method)
                    <option value="{{ $method->value }}" @selected(($filters['payment_method'] ?? '') === $method->value)>
                        {{ $method->label() }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="from" class="form-control mr-2 mb-2" value="{{ $filters['from'] ?? '' }}" title="From">
            <input type="date" name="to" class="form-control mr-2 mb-2" value="{{ $filters['to'] ?? '' }}" title="To">
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> Filter</button>
        </form>
    </div>
    <div class="col-md-3 text-md-right">
        @can('create', \App\Models\Offering::class)
            <a href="{{ route('church.offerings.create') }}" class="btn btn-primary mb-2">
                <i class="fa fa-plus"></i> Record Offering
            </a>
        @endcan
        @can('finance.approve')
            <a href="{{ route('church.finance.approvals') }}" class="btn btn-outline-primary mb-2">
                <i class="fa fa-check-circle"></i> Approvals
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
                        <th>Recorded For</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Recorded By</th>
                        <th width="130">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offerings as $offering)
                        <tr>
                            <td>
                                @if($offering->member)
                                    <span class="badge badge-light">Member</span>
                                    {{ $offering->member->full_name }}
                                    @if($offering->member->envelope_number)
                                        <br><small class="text-muted">{{ $offering->member->envelope_number }}</small>
                                    @endif
                                @else
                                    <span class="badge badge-light">General</span>
                                    {{ $offering->churchService?->displayTitle() ?? 'General Offering' }}
                                    @if($offering->churchService)
                                        <br><small class="text-muted">{{ $offering->churchService->service_date?->format('M d, Y') }}</small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $offering->offeringTypeLabel() }}</span>
                            </td>
                            <td><strong>TZS {{ number_format($offering->amount, 2) }}</strong></td>
                            <td>{{ $offering->offering_date->format('M d, Y') }}</td>
                            <td>{{ $offering->payment_method?->label() ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $offering->approval_status->badgeClass() }}">
                                    {{ $offering->approval_status->label() }}
                                </span>
                            </td>
                            <td>{{ $offering->recorder?->name ?? '—' }}</td>
                            <td>
                                <div class="btn-group">
                                    @can('view', $offering)
                                        <a href="{{ route('church.offerings.show', $offering) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update', $offering)
                                        <a href="{{ route('church.offerings.edit', $offering) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $offering)
                                        <form method="POST" action="{{ route('church.offerings.destroy', $offering) }}" class="d-inline"
                                            data-swal-confirm="Delete this offering record?"
                                            data-swal-delete
                                            data-swal-confirm-text="Yes, delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
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
                                No offering records found.
                                @can('create', \App\Models\Offering::class)
                                    <a href="{{ route('church.offerings.create') }}">Record an offering</a>.
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $offerings->links() }}
    </div>
</div>
@endsection
