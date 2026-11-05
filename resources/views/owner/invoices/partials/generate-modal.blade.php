<div class="modal fade" id="generateInvoiceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ $formAction ?? route('owner.invoices.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('owner.inv.generate') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="d-grid gap-3">
                        @if(empty($fixedChurch))
                            <div>
                                <label class="form-label" for="generate_invoice_church">{{ __('owner.church_label') }} <span class="text-danger">*</span></label>
                                <select name="church_id" id="generate_invoice_church" class="form-select" required>
                                    <option value="">{{ __('owner.inv.select_church') }}</option>
                                    @foreach($churches as $church)
                                        <option value="{{ $church->id }}" @selected(old('church_id') == $church->id)>{{ $church->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label class="form-label" for="generate_invoice_number">{{ __('owner.inv.number') }} <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_number" id="generate_invoice_number" class="form-control" maxlength="50"
                                   value="{{ old('invoice_number') }}" placeholder="20260805-022" required>
                            <div class="form-text">{{ __('owner.inv.number_help') }}</div>
                        </div>

                        <div>
                            <label class="form-label" for="invoice_type_select">{{ __('common.type') }} <span class="text-danger">*</span></label>
                            <select name="type" id="invoice_type_select" class="form-select" required>
                                <option value="installation" @selected(old('type', $defaultType ?? '') === 'installation')>{{ __('owner.inv.installation_invoice') }}</option>
                                <option value="yearly" @selected(old('type', $defaultType ?? '') === 'yearly')>{{ __('owner.inv.annual_invoice') }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label" for="generate_invoice_amount">{{ __('common.amount') }} ({{ __('common.optional') }})</label>
                            <input type="number" name="amount" id="generate_invoice_amount" class="form-control" min="0" step="0.01" value="{{ old('amount') }}" placeholder="{{ __('owner.inv.amount_placeholder') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('owner.inv.generate') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
