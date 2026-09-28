<form method="POST" action="{{ route('owner.settings.billing') }}">
    @csrf
    @method('PUT')
    <h4 class="mb-3">{{ __('owner.set.billing_rules') }}</h4>
    <p class="text-muted">{{ __('owner.set.billing_rules_help') }}</p>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.platform_currency') }}</label>
                @include('owner.settings.partials.currency-select', [
                    'fieldName' => 'currency',
                    'fieldValue' => $settings['currency'],
                    'required' => true,
                ])
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.default_trial') }}</label>
                <input type="number" name="trial_days" class="form-control" value="{{ old('trial_days', $settings['trial_days']) }}" min="0" max="90" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.tax_rate') }}</label>
                <input type="number" step="0.01" name="tax_rate" class="form-control" value="{{ old('tax_rate', $settings['tax_rate']) }}" min="0" max="100">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.grace_period') }}</label>
                <input type="number" name="grace_period_days" class="form-control" value="{{ old('grace_period_days', $settings['grace_period_days']) }}" min="0" max="30">
            </div>
        </div>
    </div>

    <hr class="my-4">
    <h4 class="mb-3">{{ __('owner.set.invoice_company') }}</h4>
    <p class="text-muted">{{ __('owner.set.invoice_company_help') }}</p>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('owner.set.company_name') }}</label>
                <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $settings['company_name']) }}" maxlength="150">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('owner.set.company_phone') }}</label>
                <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $settings['company_phone']) }}" maxlength="50">
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>{{ __('owner.set.company_address') }}</label>
                <textarea name="company_address" class="form-control" rows="2" maxlength="255">{{ old('company_address', $settings['company_address']) }}</textarea>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.company_tin') }}</label>
                <input type="text" name="company_tin" class="form-control" value="{{ old('company_tin', $settings['company_tin']) }}" maxlength="50">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.company_website') }}</label>
                <input type="text" name="company_website" class="form-control" value="{{ old('company_website', $settings['company_website']) }}" maxlength="150">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.invoice_recipient_title') }}</label>
                <input type="text" name="invoice_recipient_title" class="form-control" value="{{ old('invoice_recipient_title', $settings['invoice_recipient_title']) }}" maxlength="100">
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>{{ __('owner.set.invoice_purpose') }}</label>
                <input type="text" name="invoice_purpose" class="form-control" value="{{ old('invoice_purpose', $settings['invoice_purpose']) }}" maxlength="255">
            </div>
        </div>
    </div>

    <hr class="my-4">
    <h4 class="mb-3">{{ __('owner.set.invoice_bank') }}</h4>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.bank_name') }}</label>
                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $settings['bank_name']) }}" maxlength="100">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.bank_account_name') }}</label>
                <input type="text" name="bank_account_name" class="form-control" value="{{ old('bank_account_name', $settings['bank_account_name']) }}" maxlength="150">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.bank_account_number') }}</label>
                <input type="text" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $settings['bank_account_number']) }}" maxlength="50">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('owner.set.save_billing') }}</button>
</form>
