@extends('layouts.church')

@section('title', __('pages.pledges.show_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-handshake-o',
    'title' => __('pages.pledges.show_title'),
    'subtitle' => $pledge->member?->full_name ?? __('pages.pledges.member_fallback'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.pledges'), 'route' => 'church.pledges.index'],
        ['label' => __('pages.shared.breadcrumb_details')],
    ],
])

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.shared.information', ['module' => __('pages.pledges.item')]) }}</h3>
            <table class="table table-borderless table-sm">
                <tr>
                    <th width="180">{{ __('common.status') }}</th>
                    <td>
                        <span class="badge badge-{{ $pledge->status->badgeClass() }}">
                            {{ $pledge->status->label() }}
                        </span>
                    </td>
                </tr>
                <tr><th>{{ __('common.member') }}</th><td>{{ $pledge->member?->full_name ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.pledges.show_pledge_type') }}</th><td>{{ $pledge->pledgeTypeLabel() }}</td></tr>
                <tr><th>{{ __('pages.pledges.show_pledge_amount') }}</th><td><strong>TZS {{ number_format($pledge->pledge_amount, 2) }}</strong></td></tr>
                <tr><th>{{ __('pages.pledges.show_amount_paid') }}</th><td>TZS {{ number_format($pledge->amount_paid, 2) }}</td></tr>
                <tr><th>{{ __('pages.shared.remaining') }}</th><td>TZS {{ number_format($pledge->remainingAmount(), 2) }}</td></tr>
                <tr>
                    <th>{{ __('pages.shared.progress') }}</th>
                    <td>
                        <div class="progress" style="height: 22px; max-width: 320px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: {{ min(100, $pledge->progressPercentage()) }}%">
                                {{ $pledge->progressPercentage() }}%
                            </div>
                        </div>
                    </td>
                </tr>
                <tr><th>{{ __('pages.pledges.form_pledge_date') }}</th><td>{{ $pledge->pledge_date->format('M d, Y') }}</td></tr>
                <tr><th>{{ __('pages.shared.due_date') }}</th><td>{{ $pledge->due_date?->format('M d, Y') ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.pledges.show_payment_frequency') }}</th><td>{{ $pledge->payment_frequency?->label() ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.purpose') }}</th><td>{{ $pledge->purpose ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.notes') }}</th><td>{{ $pledge->notes ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.recorded_by') }}</th><td>{{ $pledge->recorder?->name ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.recorded_on') }}</th><td>{{ $pledge->created_at->format('M d, Y H:i') }}</td></tr>
            </table>
        </div>

        <div class="tile">
            <h3 class="tile-title">{{ __('pages.pledges.payment_history', ['count' => $pledge->payments->count()]) }}</h3>
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>{{ __('common.amount') }}</th>
                            <th>{{ __('common.date') }}</th>
                            <th>{{ __('pages.shared.payment') }}</th>
                            <th>{{ __('common.reference') }}</th>
                            <th>{{ __('common.status') }}</th>
                            <th>{{ __('pages.shared.recorded_by') }}</th>
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
                                <td colspan="6" class="text-center text-muted py-3">{{ __('pages.pledges.no_payments') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @can('recordPayment', $pledge)
            <div class="tile">
                <h3 class="tile-title">{{ __('pages.pledges.record_payment') }}</h3>
                <form method="POST" action="{{ route('church.pledges.payment', $pledge) }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('pages.shared.amount_tzs') }} *</label>
                                <input type="number" step="0.01" min="0.01" max="{{ $pledge->remainingAmount() }}"
                                    name="amount" class="form-control @error('amount') is-invalid @enderror"
                                    value="{{ old('amount') }}" required>
                                @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('pages.pledges.form_payment_date') }} *</label>
                                <input type="date" name="payment_date"
                                    class="form-control @error('payment_date') is-invalid @enderror"
                                    value="{{ old('payment_date', now()->toDateString()) }}" required>
                                @error('payment_date')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('pages.shared.payment_method') }} *</label>
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
                                <label>{{ __('pages.shared.reference_number') }}</label>
                                <input type="text" name="reference_number"
                                    class="form-control @error('reference_number') is-invalid @enderror"
                                    value="{{ old('reference_number') }}">
                                @error('reference_number')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{ __('pages.shared.notes') }}</label>
                                <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror"
                                    value="{{ old('notes') }}">
                                @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> {{ __('pages.pledges.record_payment') }}</button>
                </form>
                <p class="text-muted small mt-2 mb-0">
                    {{ __('pages.pledges.payment_pending_hint') }}
                </p>
            </div>
        @endcan
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('common.actions') }}</h3>
            <a href="{{ route('church.pledges.index') }}" class="btn btn-secondary btn-block">
                <i class="fa fa-arrow-left"></i> {{ __('pages.shared.back_to', ['module' => __('menu.pledges')]) }}
            </a>
            @can('update', $pledge)
                <a href="{{ route('church.pledges.edit', $pledge) }}" class="btn btn-primary btn-block mt-2">
                    <i class="fa fa-pencil"></i> {{ __('pages.shared.edit_item', ['item' => __('pages.pledges.item')]) }}
                </a>
            @endcan
            @can('finance.approve')
                <a href="{{ route('church.finance.approvals') }}#tab-pledge_payment" class="btn btn-success btn-block mt-2">
                    <i class="fa fa-check-circle"></i> {{ __('pages.shared.go_to_approvals') }}
                </a>
            @endcan
            @can('delete', $pledge)
                <form method="POST" action="{{ route('church.pledges.destroy', $pledge) }}" class="mt-2"
                    data-swal-confirm="{{ __('pages.shared.delete_confirm', ['item' => __('pages.pledges.item')]) }}"
                    data-swal-delete
                    data-swal-confirm-text="{{ __('common.yes_delete') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i> {{ __('pages.shared.delete_item', ['item' => __('pages.pledges.item')]) }}
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
