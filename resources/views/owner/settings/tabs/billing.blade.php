<form method="POST" action="{{ route('owner.settings.billing') }}">
    @csrf
    @method('PUT')

    <h2 class="h5 mb-1">{{ __('owner.set.billing_rules') }}</h2>
    <p class="text-muted small mb-4">{{ __('owner.set.billing_rules_help') }}</p>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="currency">{{ __('owner.set.platform_currency') }}</label>
            @include('owner.settings.partials.currency-select', [
                'fieldName' => 'currency',
                'fieldValue' => $settings['currency'],
                'required' => true,
            ])
        </div>
        <div class="col-md-4">
            <label class="form-label" for="trial_days">{{ __('owner.set.default_trial') }}</label>
            <input type="number" id="trial_days" name="trial_days" class="form-control" value="{{ old('trial_days', $settings['trial_days']) }}" min="0" max="90" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="tax_rate">{{ __('owner.set.tax_rate') }}</label>
            <input type="number" step="0.01" id="tax_rate" name="tax_rate" class="form-control" value="{{ old('tax_rate', $settings['tax_rate']) }}" min="0" max="100">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="grace_period_days">{{ __('owner.set.grace_period') }}</label>
            <input type="number" id="grace_period_days" name="grace_period_days" class="form-control" value="{{ old('grace_period_days', $settings['grace_period_days']) }}" min="0" max="30">
        </div>
    </div>

    <hr class="my-4">

    <h2 class="h5 mb-1">{{ __('owner.set.invoice_company') }}</h2>
    <p class="text-muted small mb-4">{{ __('owner.set.invoice_company_help') }}</p>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="company_name">{{ __('owner.set.company_name') }}</label>
            <input type="text" id="company_name" name="company_name" class="form-control" value="{{ old('company_name', $settings['company_name']) }}" maxlength="150">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="company_phone">{{ __('owner.set.company_phone') }}</label>
            <input type="text" id="company_phone" name="company_phone" class="form-control" value="{{ old('company_phone', $settings['company_phone']) }}" maxlength="50">
        </div>
        <div class="col-12">
            <label class="form-label" for="company_address">{{ __('owner.set.company_address') }}</label>
            <textarea id="company_address" name="company_address" class="form-control" rows="2" maxlength="255">{{ old('company_address', $settings['company_address']) }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="company_tin">{{ __('owner.set.company_tin') }}</label>
            <input type="text" id="company_tin" name="company_tin" class="form-control" value="{{ old('company_tin', $settings['company_tin']) }}" maxlength="50">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="company_website">{{ __('owner.set.company_website') }}</label>
            <input type="text" id="company_website" name="company_website" class="form-control" value="{{ old('company_website', $settings['company_website']) }}" maxlength="150">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="invoice_recipient_title">{{ __('owner.set.invoice_recipient_title') }}</label>
            <input type="text" id="invoice_recipient_title" name="invoice_recipient_title" class="form-control" value="{{ old('invoice_recipient_title', $settings['invoice_recipient_title']) }}" maxlength="100">
        </div>
        <div class="col-12">
            <label class="form-label" for="invoice_purpose">{{ __('owner.set.invoice_purpose') }}</label>
            <input type="text" id="invoice_purpose" name="invoice_purpose" class="form-control" value="{{ old('invoice_purpose', $settings['invoice_purpose']) }}" maxlength="255">
        </div>
    </div>

    <hr class="my-4">

    <h2 class="h5 mb-3">{{ __('owner.set.invoice_bank') }}</h2>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="bank_name">{{ __('owner.set.bank_name') }}</label>
            <input type="text" id="bank_name" name="bank_name" class="form-control" value="{{ old('bank_name', $settings['bank_name']) }}" maxlength="100">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="bank_account_name">{{ __('owner.set.bank_account_name') }}</label>
            <input type="text" id="bank_account_name" name="bank_account_name" class="form-control" value="{{ old('bank_account_name', $settings['bank_account_name']) }}" maxlength="150">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="bank_account_number">{{ __('owner.set.bank_account_number') }}</label>
            <input type="text" id="bank_account_number" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $settings['bank_account_number']) }}" maxlength="50">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> {{ __('owner.set.save_billing') }}
        </button>
    </div>
</form>
