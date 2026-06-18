@extends('layouts.church')

@section('title', 'Tithe Details')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-money"></i> Tithe Details</h1>
        <p>{{ $tithe->member?->full_name ?? 'Member tithe' }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.tithes.index') }}">Tithes</a></li>
        <li class="breadcrumb-item">Details</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">Tithe Information</h3>
            <table class="table table-borderless table-sm">
                <tr>
                    <th width="180">Status</th>
                    <td>
                        <span class="badge badge-{{ $tithe->approval_status->badgeClass() }}">
                            {{ $tithe->approval_status->label() }}
                        </span>
                    </td>
                </tr>
                <tr><th>Member</th><td>{{ $tithe->member?->full_name ?? '—' }}</td></tr>
                <tr><th>Envelope</th><td>{{ $tithe->member?->envelope_number ?? '—' }}</td></tr>
                <tr><th>Amount</th><td><strong>TZS {{ number_format($tithe->amount, 2) }}</strong></td></tr>
                <tr><th>Tithe Date</th><td>{{ $tithe->tithe_date->format('M d, Y') }}</td></tr>
                <tr><th>Payment Method</th><td>{{ $tithe->payment_method?->label() ?? '—' }}</td></tr>
                <tr><th>Reference</th><td>{{ $tithe->reference_number ?? '—' }}</td></tr>
                <tr><th>Notes</th><td>{{ $tithe->notes ?? '—' }}</td></tr>
                <tr><th>Recorded By</th><td>{{ $tithe->recorder?->name ?? '—' }}</td></tr>
                <tr><th>Recorded On</th><td>{{ $tithe->created_at->format('M d, Y H:i') }}</td></tr>
                @if($tithe->approved_at)
                    <tr><th>Approved By</th><td>{{ $tithe->approver?->name ?? '—' }}</td></tr>
                    <tr><th>Approved On</th><td>{{ $tithe->approved_at->format('M d, Y H:i') }}</td></tr>
                    @if($tithe->approval_notes)
                        <tr><th>Approval Notes</th><td>{{ $tithe->approval_notes }}</td></tr>
                    @endif
                @endif
                @if($tithe->rejection_reason)
                    <tr><th>Rejection Reason</th><td class="text-danger">{{ $tithe->rejection_reason }}</td></tr>
                @endif
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">Actions</h3>
            <a href="{{ route('church.tithes.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> Back to Tithes
            </a>
            @can('update', $tithe)
                <a href="{{ route('church.tithes.edit', $tithe) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> Edit Tithe
                </a>
            @endcan
            @if($tithe->isPendingApproval() && auth()->user()->can('finance.approve'))
                <a href="{{ route('church.finance.approvals') }}" class="btn btn-success btn-block mt-2">
                    <i class="fa fa-check-circle"></i> Go to Approvals
                </a>
            @endif
            @can('delete', $tithe)
                <form method="POST" action="{{ route('church.tithes.destroy', $tithe) }}" class="mt-2"
                    data-swal-confirm="Delete this tithe record?"
                    data-swal-delete
                    data-swal-confirm-text="Yes, delete">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> Delete Tithe
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
