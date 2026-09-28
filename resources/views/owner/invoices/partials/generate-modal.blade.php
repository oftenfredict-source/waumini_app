<div class="modal fade" id="generateInvoiceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ $formAction ?? route('owner.invoices.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('owner.inv.generate') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if(empty($fixedChurch))
                        <div class="form-group">
                            <label>{{ __('owner.church_label') }} <span class="text-danger">*</span></label>
                            <select name="church_id" class="form-control" required>
                                <option value="">{{ __('owner.inv.select_church') }}</option>
                                @foreach($churches as $church)
                                    <option value="{{ $church->id }}" @selected(old('church_id') == $church->id)>{{ $church->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="form-group">
                        <label>{{ __('owner.inv.number') }} <span class="text-danger">*</span></label>
                        <input type="text" name="invoice_number" class="form-control" maxlength="50"
                               value="{{ old('invoice_number') }}" placeholder="20260805-022" required>
                        <small class="text-muted">{{ __('owner.inv.number_help') }}</small>
                    </div>

                    <div class="form-group">
                        <label>{{ __('common.type') }} <span class="text-danger">*</span></label>
                        <select name="type" id="invoice_type_select" class="form-control" required>
                            <option value="installation" @selected(old('type', $defaultType ?? '') === 'installation')>{{ __('owner.inv.installation_invoice') }}</option>
                            <option value="yearly" @selected(old('type', $defaultType ?? '') === 'yearly')>{{ __('owner.inv.annual_invoice') }}</option>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label>{{ __('common.amount') }} ({{ __('common.optional') }})</label>
                        <input type="number" name="amount" class="form-control" min="0" step="0.01" value="{{ old('amount') }}" placeholder="{{ __('owner.inv.amount_placeholder') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('owner.inv.generate') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
