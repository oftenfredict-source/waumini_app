@php
    $p = $package;
@endphp

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('owner.set.package_name') }}</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $p->name ?? '') }}" required>
        </div>
    </div>
    @if($p)
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.slug') }}</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $p->slug) }}">
            </div>
        </div>
    @endif
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('owner.set.sort_order') }}</label>
            <input type="number" name="sort_order" class="form-control"
                value="{{ old('sort_order', $p ? $p->sort_order : (($packages->max('sort_order') ?? 0) + 1)) }}" min="0">
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label>{{ __('owner.set.description') }}</label>
            <textarea name="description" class="form-control" rows="2">{{ old('description', $p->description ?? '') }}</textarea>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('owner.set.installation_price', ['currency' => $settings['currency']]) }}</label>
            <input type="number" step="0.01" name="installation_price" class="form-control"
                value="{{ old('installation_price', $p->installation_price ?? 0) }}" required>
            <small class="text-muted">{{ __('owner.set.installation_help') }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('owner.set.yearly_price', ['currency' => $settings['currency']]) }}</label>
            <input type="number" step="0.01" name="yearly_price" class="form-control"
                value="{{ old('yearly_price', $p->yearly_price ?? 0) }}" required>
            <small class="text-muted">{{ __('owner.set.yearly_help') }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('owner.set.trial_days_label') }}</label>
            <input type="number" name="trial_days" class="form-control"
                value="{{ old('trial_days', $p->trial_days ?? $settings['trial_days']) }}" min="0" max="90" required>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group pt-4">
            <div class="animated-checkbox">
                <label>
                    <input type="checkbox" name="is_active" value="1"
                        @if(old('is_active', $p ? $p->is_active : true)) checked @endif>
                    <span class="label-text">{{ __('owner.set.package_active') }}</span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('owner.set.max_members') }}</label>
            <input type="number" name="max_members" class="form-control"
                value="{{ old('max_members', $p->max_members ?? '') }}" placeholder="{{ __('owner.set.unlimited') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('owner.set.max_sms') }}</label>
            <input type="number" name="max_sms_monthly" class="form-control"
                value="{{ old('max_sms_monthly', $p->max_sms_monthly ?? '') }}" placeholder="{{ __('owner.set.unlimited') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('owner.set.max_storage') }}</label>
            <input type="number" name="max_storage_mb" class="form-control"
                value="{{ old('max_storage_mb', $p->max_storage_mb ?? '') }}" placeholder="{{ __('owner.set.unlimited') }}">
        </div>
    </div>
</div>
