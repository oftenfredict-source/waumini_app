@php
    $p = $package;
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label" for="{{ ($prefix ?? '') }}name">{{ __('owner.set.package_name') }}</label>
        <input type="text" id="{{ ($prefix ?? '') }}name" name="name" class="form-control" value="{{ old('name', $p->name ?? '') }}" required>
    </div>
    @if($p)
        <div class="col-md-4">
            <label class="form-label" for="{{ ($prefix ?? '') }}slug">{{ __('owner.set.slug') }}</label>
            <input type="text" id="{{ ($prefix ?? '') }}slug" name="slug" class="form-control" value="{{ old('slug', $p->slug) }}">
        </div>
    @endif
    <div class="col-md-4">
        <label class="form-label" for="{{ ($prefix ?? '') }}sort_order">{{ __('owner.set.sort_order') }}</label>
        <input type="number" id="{{ ($prefix ?? '') }}sort_order" name="sort_order" class="form-control"
            value="{{ old('sort_order', $p ? $p->sort_order : (($packages->max('sort_order') ?? 0) + 1)) }}" min="0">
    </div>
    <div class="col-12">
        <label class="form-label" for="{{ ($prefix ?? '') }}description">{{ __('owner.set.description') }}</label>
        <textarea id="{{ ($prefix ?? '') }}description" name="description" class="form-control" rows="2">{{ old('description', $p->description ?? '') }}</textarea>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="{{ ($prefix ?? '') }}installation_price">{{ __('owner.set.installation_price', ['currency' => $settings['currency']]) }}</label>
        <input type="number" step="0.01" id="{{ ($prefix ?? '') }}installation_price" name="installation_price" class="form-control"
            value="{{ old('installation_price', $p->installation_price ?? 0) }}" required>
        <div class="form-text">{{ __('owner.set.installation_help') }}</div>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="{{ ($prefix ?? '') }}yearly_price">{{ __('owner.set.yearly_price', ['currency' => $settings['currency']]) }}</label>
        <input type="number" step="0.01" id="{{ ($prefix ?? '') }}yearly_price" name="yearly_price" class="form-control"
            value="{{ old('yearly_price', $p->yearly_price ?? 0) }}" required>
        <div class="form-text">{{ __('owner.set.yearly_help') }}</div>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="{{ ($prefix ?? '') }}trial_days">{{ __('owner.set.trial_days_label') }}</label>
        <input type="number" id="{{ ($prefix ?? '') }}trial_days" name="trial_days" class="form-control"
            value="{{ old('trial_days', $p->trial_days ?? $settings['trial_days']) }}" min="0" max="90" required>
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check mb-2">
            <input type="checkbox" class="form-check-input" id="{{ ($prefix ?? '') }}is_active" name="is_active" value="1"
                @checked(old('is_active', $p ? $p->is_active : true))>
            <label class="form-check-label" for="{{ ($prefix ?? '') }}is_active">{{ __('owner.set.package_active') }}</label>
        </div>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="{{ ($prefix ?? '') }}max_members">{{ __('owner.set.max_members') }}</label>
        <input type="number" id="{{ ($prefix ?? '') }}max_members" name="max_members" class="form-control"
            value="{{ old('max_members', $p->max_members ?? '') }}" placeholder="{{ __('owner.set.unlimited') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="{{ ($prefix ?? '') }}max_sms_monthly">{{ __('owner.set.max_sms') }}</label>
        <input type="number" id="{{ ($prefix ?? '') }}max_sms_monthly" name="max_sms_monthly" class="form-control"
            value="{{ old('max_sms_monthly', $p->max_sms_monthly ?? '') }}" placeholder="{{ __('owner.set.unlimited') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="{{ ($prefix ?? '') }}max_storage_mb">{{ __('owner.set.max_storage') }}</label>
        <input type="number" id="{{ ($prefix ?? '') }}max_storage_mb" name="max_storage_mb" class="form-control"
            value="{{ old('max_storage_mb', $p->max_storage_mb ?? '') }}" placeholder="{{ __('owner.set.unlimited') }}">
    </div>
</div>
