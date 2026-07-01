<form method="POST" action="{{ route('owner.settings.churches') }}">
    @csrf
    @method('PUT')
    <h4 class="mb-3">{{ __('owner.set.church_registration') }}</h4>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <div class="animated-checkbox">
                    <label>
                        <input type="checkbox" name="allow_registration" value="1" @checked(old('allow_registration', $settings['allow_registration']))>
                        <span class="label-text">{{ __('owner.set.allow_registration') }}</span>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <div class="animated-checkbox">
                    <label>
                        <input type="checkbox" name="require_approval" value="1" @checked(old('require_approval', $settings['require_approval']))>
                        <span class="label-text">{{ __('owner.set.require_approval') }}</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('owner.set.default_timezone') }}</label>
                <input type="text" name="default_timezone" class="form-control" value="{{ old('default_timezone', $settings['default_timezone']) }}" required>
            </div>
            <div class="form-group">
                <label>{{ __('owner.set.default_country') }}</label>
                <input type="text" name="default_country" class="form-control" value="{{ old('default_country', $settings['default_country']) }}" placeholder="{{ __('owner.set.country_placeholder') }}">
            </div>
            <div class="form-group">
                <label>{{ __('owner.set.default_currency') }}</label>
                @include('owner.settings.partials.currency-select', [
                    'fieldName' => 'default_currency',
                    'fieldValue' => $settings['default_currency'],
                    'required' => true,
                ])
            </div>
        </div>
    </div>

    <hr>
    <h5>{{ __('owner.set.tenant_domains') }}</h5>
    <p>{{ __('owner.set.subdomain_info', ['pattern' => '{church-slug}.' . $baseDomain]) }}</p>
    <p class="text-muted">{{ __('owner.set.env_domain', ['domain' => $baseDomain]) }}</p>
    <p class="text-muted">{{ __('owner.set.dns_note') }}</p>

    <button type="submit" class="btn btn-primary mt-2"><i class="fa fa-save"></i> {{ __('owner.set.save_church') }}</button>
</form>
