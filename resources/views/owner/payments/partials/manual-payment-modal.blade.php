<div class="modal fade" id="manualPaymentModal" tabindex="-1" aria-hidden="true"
     @if($errors->any() && old('_form') === 'manual_payment') data-show-on-load="1" @endif>
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('owner.payments.store') }}">
                @csrf
                <input type="hidden" name="_form" value="manual_payment">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('owner.pay.record_manual') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">{{ __('owner.pay.record_manual_help') }}</p>
                    <div class="alert border-0 mb-3" style="background:rgba(8,145,178,.1);color:#0e7490;">
                        <i class="bi bi-info-circle me-1"></i>
                        {{ __('owner.pay.activate_hint') }}
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="manual_payment_church">{{ __('owner.church_label') }} <span class="text-danger">*</span></label>
                            <select name="church_id" id="manual_payment_church" class="form-select @error('church_id') is-invalid @enderror" required>
                                <option value="">{{ __('owner.inv.select_church') }}</option>
                                @foreach($churches as $church)
                                    <option value="{{ $church->id }}" @selected(old('church_id') == $church->id)>{{ $church->name }}</option>
                                @endforeach
                            </select>
                            @error('church_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="manual_payment_invoice">{{ __('owner.pay.link_invoice') }}</label>
                            <select name="invoice_id" id="manual_payment_invoice" class="form-select @error('invoice_id') is-invalid @enderror">
                                <option value="">{{ __('owner.pay.no_invoice') }}</option>
                                @foreach($pendingInvoices as $invoice)
                                    <option
                                        value="{{ $invoice->id }}"
                                        data-church-id="{{ $invoice->church_id }}"
                                        data-amount="{{ $invoice->total_amount }}"
                                        @selected(old('invoice_id') == $invoice->id)
                                    >
                                        {{ $invoice->invoice_number }} — {{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }} ({{ ucfirst($invoice->type) }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">{{ __('owner.pay.link_invoice_help') }}</div>
                            @error('invoice_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="manual_payment_amount">{{ __('common.amount') }} <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="manual_payment_amount"
                                   class="form-control @error('amount') is-invalid @enderror"
                                   min="0.01" step="0.01" value="{{ old('amount') }}" required>
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="manual_payment_type">{{ __('common.type') }}</label>
                            <select name="type" id="manual_payment_type" class="form-select @error('type') is-invalid @enderror">
                                <option value="installation" @selected(old('type', 'installation') === 'installation')>{{ __('owner.pay.type_installation') }}</option>
                                <option value="yearly" @selected(old('type') === 'yearly')>{{ __('owner.pay.type_yearly') }}</option>
                                <option value="other" @selected(old('type') === 'other')>{{ __('owner.pay.type_other') }}</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="manual_payment_method">{{ __('owner.pay.method') }} <span class="text-danger">*</span></label>
                            <select name="method" id="manual_payment_method" class="form-select @error('method') is-invalid @enderror" required>
                                <option value="cash" @selected(old('method', 'cash') === 'cash')>{{ __('owner.pay.methods.cash') }}</option>
                                <option value="bank_transfer" @selected(old('method') === 'bank_transfer')>{{ __('owner.pay.methods.bank_transfer') }}</option>
                                <option value="mobile_money" @selected(old('method') === 'mobile_money')>{{ __('owner.pay.methods.mobile_money') }}</option>
                                <option value="cheque" @selected(old('method') === 'cheque')>{{ __('owner.pay.methods.cheque') }}</option>
                                <option value="other" @selected(old('method') === 'other')>{{ __('owner.pay.methods.other') }}</option>
                            </select>
                            @error('method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="manual_payment_reference">{{ __('common.reference') }}</label>
                            <input type="text" name="provider_reference" id="manual_payment_reference" class="form-control @error('provider_reference') is-invalid @enderror"
                                   maxlength="150" value="{{ old('provider_reference') }}"
                                   placeholder="{{ __('owner.pay.reference_placeholder') }}">
                            @error('provider_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="manual_payment_paid_at">{{ __('owner.pay.paid_at') }}</label>
                            <input type="date" name="paid_at" id="manual_payment_paid_at" class="form-control @error('paid_at') is-invalid @enderror"
                                   value="{{ old('paid_at', now()->toDateString()) }}" max="{{ now()->toDateString() }}">
                            @error('paid_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="manual_payment_notes">{{ __('pages.shared.notes') }}</label>
                            <textarea name="notes" id="manual_payment_notes" class="form-control @error('notes') is-invalid @enderror" rows="3" maxlength="500">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> {{ __('owner.pay.record_manual') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var churchSelect = document.getElementById('manual_payment_church');
    var invoiceSelect = document.getElementById('manual_payment_invoice');
    var amountInput = document.getElementById('manual_payment_amount');
    var modal = document.getElementById('manualPaymentModal');

    if (!churchSelect || !invoiceSelect) return;

    function filterInvoices() {
        var churchId = churchSelect.value;
        var options = invoiceSelect.querySelectorAll('option[data-church-id]');
        var keepSelected = false;

        options.forEach(function (option) {
            var match = !churchId || option.getAttribute('data-church-id') === churchId;
            option.hidden = !match;
            option.disabled = !match;
            if (match && option.selected) keepSelected = true;
        });

        if (!keepSelected) {
            invoiceSelect.value = '';
        }
    }

    function fillAmountFromInvoice() {
        var selected = invoiceSelect.options[invoiceSelect.selectedIndex];
        if (!selected || !selected.value) {
            if (amountInput) amountInput.required = true;
            return;
        }
        var amount = selected.getAttribute('data-amount');
        if (amount && amountInput) {
            amountInput.value = amount;
            amountInput.required = false;
        }
    }

    churchSelect.addEventListener('change', filterInvoices);
    invoiceSelect.addEventListener('change', fillAmountFromInvoice);
    filterInvoices();
    fillAmountFromInvoice();

    if (modal && modal.getAttribute('data-show-on-load') === '1' && window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(modal).show();
    }
})();
</script>
@endpush
