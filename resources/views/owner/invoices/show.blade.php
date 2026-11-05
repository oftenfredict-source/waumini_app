@extends('layouts.owner')

@section('title', __('owner.inv.title').' '.$invoice->invoice_number)

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 d-print-none">
    <div>
        <h1 class="h3 mb-1">{{ __('owner.inv.title') }} #{{ $invoice->invoice_number }}</h1>
        <p class="text-muted mb-0">{{ $invoice->church->name }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('owner.invoices.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> {{ __('owner.invoices') }}
        </a>
        <a href="{{ route('owner.invoices.pdf', $invoice) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-download me-1"></i> {{ __('owner.inv.download_pdf') }}
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> {{ __('owner.inv.print') }}
        </button>
        @can('markPaid', $invoice)
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#markPaidModal">
                <i class="bi bi-check-lg me-1"></i> {{ __('owner.inv.mark_as_paid') }}
            </button>
        @endcan
    </div>
</div>

<div class="card owner-panel-card mb-4">
    <div class="card-body">
        @include('owner.invoices.partials.document', ['invoice' => $invoice, 'company' => $company])
    </div>
</div>

@if($invoice->payment)
    <div class="alert border-0 d-print-none" style="background:rgba(22,163,74,.1);color:#166534;">
        <i class="bi bi-check-circle me-1"></i>
        {{ __('owner.inv.linked_payment') }}:
        {{ $invoice->payment->currency }} {{ number_format($invoice->payment->amount, 2) }}
        ({{ $invoice->payment->paid_at?->format('M d, Y H:i') }})
    </div>
@endif

@can('markPaid', $invoice)
<div class="modal fade d-print-none" id="markPaidModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('owner.invoices.mark-paid', $invoice) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('owner.inv.mark_as_paid') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">{{ __('owner.inv.mark_paid_help') }}</p>
                    <div class="d-grid gap-3">
                        <div>
                            <label class="form-label" for="mark_paid_method">{{ __('owner.pay.method') }}</label>
                            <select name="method" id="mark_paid_method" class="form-select">
                                <option value="cash">{{ __('owner.pay.methods.cash') }}</option>
                                <option value="bank_transfer">{{ __('owner.pay.methods.bank_transfer') }}</option>
                                <option value="mobile_money">{{ __('owner.pay.methods.mobile_money') }}</option>
                                <option value="cheque">{{ __('owner.pay.methods.cheque') }}</option>
                                <option value="other">{{ __('owner.pay.methods.other') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="mark_paid_reference">{{ __('common.reference') }}</label>
                            <input type="text" name="provider_reference" id="mark_paid_reference" class="form-control" maxlength="150">
                        </div>
                        <div>
                            <label class="form-label" for="mark_paid_notes">{{ __('pages.shared.notes') }}</label>
                            <textarea name="notes" id="mark_paid_notes" class="form-control" rows="3" maxlength="500"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('owner.inv.confirm_paid') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
