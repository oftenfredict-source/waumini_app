@extends('layouts.owner')

@section('title', __('owner.inv.title').' '.$invoice->invoice_number)

@section('content')
<div class="app-title d-print-none">
    <div>
        <h1><i class="fa fa-file-text-o"></i> {{ __('owner.inv.title') }} #{{ $invoice->invoice_number }}</h1>
        <p>{{ $invoice->church->name }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.invoices.index') }}">{{ __('owner.invoices') }}</a></li>
        <li class="breadcrumb-item">{{ $invoice->invoice_number }}</li>
    </ul>
</div>

<div class="row mb-3 d-print-none">
    <div class="col-md-12 text-right">
        <a href="{{ route('owner.invoices.pdf', $invoice) }}" class="btn btn-primary">
            <i class="fa fa-download"></i> {{ __('owner.inv.download_pdf') }}
        </a>
        <button type="button" class="btn btn-secondary" onclick="window.print()">
            <i class="fa fa-print"></i> {{ __('owner.inv.print') }}
        </button>
        @can('markPaid', $invoice)
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#markPaidModal">
                <i class="fa fa-check"></i> {{ __('owner.inv.mark_as_paid') }}
            </button>
        @endcan
    </div>
</div>

<div class="tile">
    @include('owner.invoices.partials.document', ['invoice' => $invoice, 'company' => $company])
</div>

@if($invoice->payment)
    <div class="alert alert-success d-print-none mt-3">
        <i class="fa fa-check-circle"></i>
        {{ __('owner.inv.linked_payment') }}:
        {{ $invoice->payment->currency }} {{ number_format($invoice->payment->amount, 2) }}
        ({{ $invoice->payment->paid_at?->format('M d, Y H:i') }})
    </div>
@endif

@can('markPaid', $invoice)
<div class="modal fade d-print-none" id="markPaidModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('owner.invoices.mark-paid', $invoice) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('owner.inv.mark_as_paid') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">{{ __('owner.inv.mark_paid_help') }}</p>
                    <div class="form-group">
                        <label>{{ __('owner.pay.method') }}</label>
                        <select name="method" class="form-control">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="cheque">Cheque</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('common.reference') }}</label>
                        <input type="text" name="provider_reference" class="form-control" maxlength="150">
                    </div>
                    <div class="form-group mb-0">
                        <label>{{ __('pages.shared.notes') }}</label>
                        <textarea name="notes" class="form-control" rows="3" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('owner.inv.confirm_paid') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
