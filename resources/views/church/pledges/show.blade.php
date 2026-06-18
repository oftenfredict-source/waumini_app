@extends('layouts.church')

@section('title', 'Pledge Details')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-handshake-o"></i> Pledge Details</h1>
        <p>{{ $pledge->member?->full_name ?? 'Member pledge' }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.pledges.index') }}">Pledges</a></li>
        <li class="breadcrumb-item">Details</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">Pledge Information</h3>
            <table class="table table-borderless table-sm">
                <tr>
                    <th width="180">Status</th>
                    <td>
                        <span class="badge badge-{{ $pledge->status->badgeClass() }}">
                            {{ $pledge->status->label() }}
                        </span>
                    </td>
                </tr>
                <tr><th>Member</th><td>{{ $pledge->member?->full_name ?? '—' }}</td></tr>
                <tr><th>Pledge Type</th><td>{{ $pledge->pledgeTypeLabel() }}</td></tr>
                <tr><th>Pledge Amount</th><td><strong>TZS {{ number_format($pledge->pledge_amount, 2) }}</strong></td></tr>
                <tr><th>Amount Paid</th><td>TZS {{ number_format($pledge->amount_paid, 2) }}</td></tr>
                <tr><th>Remaining</th><td>TZS {{ number_format($pledge->remainingAmount(), 2) }}</td></tr>
                <tr>
                    <th>Progress</th>
                    <td>
                        <div class="progress" style="height: 22px; max-width: 320px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: {{ min(100, $pledge->progressPercentage()) }}%">
                                {{ $pledge->progressPercentage() }}%
                            </div>
                        </div>
                    </td>
                </tr>
                <tr><th>Pledge Date</th><td>{{ $pledge->pledge_date->format('M d, Y') }}</td></tr>
                <tr><th>Due Date</th><td>{{ $pledge->due_date?->format('M d, Y') ?? '—' }}</td></tr>
                <tr><th>Payment Frequency</th><td>{{ $pledge->payment_frequency?->label() ?? '—' }}</td></tr>
                <tr><th>Purpose</th><td>{{ $pledge->purpose ?? '—' }}</td></tr>
                <tr><th>Notes</th><td>{{ $pledge->notes ?? '—' }}</td></tr>
                <tr><th>Recorded By</th><td>{{ $pledge->recorder?->name ?? '—' }}</td></tr>
                <tr><th>Recorded On</th><td>{{ $pledge->created_at->format('M d, Y H:i') }}</td></tr>
            </table>
        </div>

        <div class="tile">
            <h3 class="tile-title">Payment History ({{ $pledge->payments->count() }})</h3>
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Payment</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pledge->payments as $payment)
                            <tr>
                                <td><strong>TZS {{ number_format($payment->amount, 2) }}</strong></td>
                                <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                <td>{{ $payment->payment_method?->label() ?? '—' }}</td>
                                <td>{{ $payment->reference_number ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-{{ $payment->approval_status->badgeClass() }}">
                                        {{ $payment->approval_status->label() }}
                                    </span>
                                </td>
                                <td>{{ $payment->recorder?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No payments recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @can('recordPayment', $pledge)
            <div class="tile">
                <h3 class="tile-title">Record Payment</h3>
                <form method="POST" action="{{ route('church.pledges.payment', $pledge) }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Amount (TZS) *</label>
                                <input type="number" step="0.01" min="0.01" max="{{ $pledge->remainingAmount() }}"
                                    name="amount" class="form-control @error('amount') is-invalid @enderror"
                                    value="{{ old('amount') }}" required>
                                @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Payment Date *</label>
                                <input type="date" name="payment_date"
                                    class="form-control @error('payment_date') is-invalid @enderror"
                                    value="{{ old('payment_date', now()->toDateString()) }}" required>
                                @error('payment_date')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Payment Method *</label>
                                <select name="payment_method" id="payment_method"
                                    class="form-control @error('payment_method') is-invalid @enderror" required>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method->value }}" @selected(old('payment_method') === $method->value)>
                                            {{ $method->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-3" id="referenceGroup">
                            <div class="form-group">
                                <label>Reference Number</label>
                                <input type="text" name="reference_number"
                                    class="form-control @error('reference_number') is-invalid @enderror"
                                    value="{{ old('reference_number') }}">
                                @error('reference_number')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Notes</label>
                                <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror"
                                    value="{{ old('notes') }}">
                                @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Record Payment</button>
                </form>
                <p class="text-muted small mt-2 mb-0">
                    Payments are submitted as pending and must be approved before they count toward the pledge balance.
                </p>
            </div>
        @endcan
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">Actions</h3>
            <a href="{{ route('church.pledges.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> Back to Pledges
            </a>
            @can('update', $pledge)
                <a href="{{ route('church.pledges.edit', $pledge) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> Edit Pledge
                </a>
            @endcan
            @can('finance.approve')
                <a href="{{ route('church.finance.approvals') }}#tab-pledge_payment" class="btn btn-success btn-block mt-2">
                    <i class="fa fa-check-circle"></i> Go to Approvals
                </a>
            @endcan
            @can('delete', $pledge)
                <form method="POST" action="{{ route('church.pledges.destroy', $pledge) }}" class="mt-2"
                    data-swal-confirm="Delete this pledge record?"
                    data-swal-delete
                    data-swal-confirm-text="Yes, delete">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> Delete Pledge
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const paymentSelect = document.getElementById('payment_method');
    const referenceGroup = document.getElementById('referenceGroup');
    if (!paymentSelect || !referenceGroup) return;

    function toggleReference() {
        const needsRef = ['bank_transfer', 'mobile_money', 'cheque'].includes(paymentSelect.value);
        referenceGroup.style.display = needsRef ? '' : 'none';
    }

    paymentSelect.addEventListener('change', toggleReference);
    toggleReference();
});
</script>
@endpush
