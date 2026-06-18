<form method="POST" action="{{ route('owner.settings.billing') }}">
    @csrf
    @method('PUT')
    <h4 class="mb-3">Default Billing Rules</h4>
    <p class="text-muted">These defaults apply to all churches unless overridden per package.</p>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Platform Currency</label>
                @include('owner.settings.partials.currency-select', [
                    'fieldName' => 'currency',
                    'fieldValue' => $settings['currency'],
                    'required' => true,
                ])
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Default Trial Days</label>
                <input type="number" name="trial_days" class="form-control" value="{{ old('trial_days', $settings['trial_days']) }}" min="0" max="90" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Tax Rate (%)</label>
                <input type="number" step="0.01" name="tax_rate" class="form-control" value="{{ old('tax_rate', $settings['tax_rate']) }}" min="0" max="100">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Grace Period (days after expiry)</label>
                <input type="number" name="grace_period_days" class="form-control" value="{{ old('grace_period_days', $settings['grace_period_days']) }}" min="0" max="30">
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Billing Settings</button>
</form>
