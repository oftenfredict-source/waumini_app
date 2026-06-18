@extends('layouts.church')

@section('title', 'Offering Details')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-gift"></i> Offering Details</h1>
        <p>{{ $offering->contributorLabel() }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.offerings.index') }}">Offerings</a></li>
        <li class="breadcrumb-item">Details</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">Offering Information</h3>
            <table class="table table-borderless table-sm">
                <tr>
                    <th width="180">Status</th>
                    <td>
                        <span class="badge badge-{{ $offering->approval_status->badgeClass() }}">
                            {{ $offering->approval_status->label() }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th width="180">Recorded For</th>
                    <td>
                        @if($offering->member)
                            <span class="badge badge-primary">Member</span>
                            {{ $offering->member->full_name }}
                            @if($offering->member->envelope_number)
                                <small class="text-muted">({{ $offering->member->envelope_number }})</small>
                            @endif
                        @else
                            <span class="badge badge-info">General Offering</span>
                            @if($offering->churchService)
                                {{ $offering->churchService->offeringSelectionLabel() }}
                            @else
                                Not linked to a service
                            @endif
                        @endif
                    </td>
                </tr>
                <tr><th>Offering Type</th><td>{{ $offering->offeringTypeLabel() }}</td></tr>
                <tr><th>Amount</th><td><strong>TZS {{ number_format($offering->amount, 2) }}</strong></td></tr>
                <tr><th>Offering Date</th><td>{{ $offering->offering_date->format('M d, Y') }}</td></tr>
                <tr><th>Payment Method</th><td>{{ $offering->payment_method?->label() ?? '—' }}</td></tr>
                <tr><th>Reference</th><td>{{ $offering->reference_number ?? '—' }}</td></tr>
                <tr><th>Notes</th><td>{{ $offering->notes ?? '—' }}</td></tr>
                <tr><th>Recorded By</th><td>{{ $offering->recorder?->name ?? '—' }}</td></tr>
                <tr><th>Recorded On</th><td>{{ $offering->created_at->format('M d, Y H:i') }}</td></tr>
                @if($offering->approved_at)
                    <tr><th>Approved By</th><td>{{ $offering->approver?->name ?? '—' }}</td></tr>
                    <tr><th>Approved On</th><td>{{ $offering->approved_at->format('M d, Y H:i') }}</td></tr>
                    @if($offering->approval_notes)
                        <tr><th>Approval Notes</th><td>{{ $offering->approval_notes }}</td></tr>
                    @endif
                @endif
                @if($offering->rejection_reason)
                    <tr><th>Rejection Reason</th><td class="text-danger">{{ $offering->rejection_reason }}</td></tr>
                @endif
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">Actions</h3>
            <a href="{{ route('church.offerings.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> Back to Offerings
            </a>
            @can('update', $offering)
                <a href="{{ route('church.offerings.edit', $offering) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> Edit Offering
                </a>
            @endcan
            @if($offering->isPendingApproval() && auth()->user()->can('finance.approve'))
                <a href="{{ route('church.finance.approvals') }}" class="btn btn-success btn-block mt-2">
                    <i class="fa fa-check-circle"></i> Go to Approvals
                </a>
            @endif
            @can('delete', $offering)
                <form method="POST" action="{{ route('church.offerings.destroy', $offering) }}" class="mt-2"
                    data-swal-confirm="Delete this offering record?"
                    data-swal-delete
                    data-swal-confirm-text="Yes, delete">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> Delete Offering
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
