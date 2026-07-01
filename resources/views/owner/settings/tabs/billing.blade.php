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
    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('owner.set.save_billing') }}</button>
</form>
