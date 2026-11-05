<form method="POST" action="{{ route('owner.settings.churches') }}">
    @csrf
    @method('PUT')

    <h2 class="h5 mb-3">{{ __('owner.set.church_registration') }}</h2>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="allow_registration" name="allow_registration" value="1" @checked(old('allow_registration', $settings['allow_registration']))>
                <label class="form-check-label" for="allow_registration">{{ __('owner.set.allow_registration') }}</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="require_approval" name="require_approval" value="1" @checked(old('require_approval', $settings['require_approval']))>
                <label class="form-check-label" for="require_approval">{{ __('owner.set.require_approval') }}</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label" for="default_timezone">{{ __('owner.set.default_timezone') }}</label>
                <input type="text" id="default_timezone" name="default_timezone" class="form-control" value="{{ old('default_timezone', $settings['default_timezone']) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="default_country">{{ __('owner.set.default_country') }}</label>
                <input type="text" id="default_country" name="default_country" class="form-control" value="{{ old('default_country', $settings['default_country']) }}" placeholder="{{ __('owner.set.country_placeholder') }}">
            </div>
            <div>
                <label class="form-label" for="default_currency">{{ __('owner.set.default_currency') }}</label>
                @include('owner.settings.partials.currency-select', [
                    'fieldName' => 'default_currency',
                    'fieldValue' => $settings['default_currency'],
                    'required' => true,
                ])
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h2 class="h5 mb-2">{{ __('owner.set.tenant_domains') }}</h2>
    <div class="alert border-0 mb-3" style="background:rgba(148,0,0,.06);color:#4a1a1a;">
        <div class="mb-1">{{ __('owner.set.subdomain_info', ['pattern' => '{church-slug}.' . $baseDomain]) }}</div>
        <div class="small mb-1">{{ __('owner.set.env_domain', ['domain' => $baseDomain]) }}</div>
        <div class="small mb-0">{{ __('owner.set.dns_note') }}</div>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save me-1"></i> {{ __('owner.set.save_church') }}
    </button>
</form>
