<form method="POST" action="{{ route('owner.settings.churches') }}">
    @csrf
    @method('PUT')
    <h4 class="mb-3">Church Registration & Defaults</h4>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <div class="animated-checkbox">
                    <label>
                        <input type="checkbox" name="allow_registration" value="1" @checked(old('allow_registration', $settings['allow_registration']))>
                        <span class="label-text">Allow new church registration</span>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <div class="animated-checkbox">
                    <label>
                        <input type="checkbox" name="require_approval" value="1" @checked(old('require_approval', $settings['require_approval']))>
                        <span class="label-text">Require owner approval before church goes live</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Default Timezone (new churches)</label>
                <input type="text" name="default_timezone" class="form-control" value="{{ old('default_timezone', $settings['default_timezone']) }}" required>
            </div>
            <div class="form-group">
                <label>Default Country</label>
                <input type="text" name="default_country" class="form-control" value="{{ old('default_country', $settings['default_country']) }}" placeholder="Tanzania">
            </div>
            <div class="form-group">
                <label>Default Currency (new churches)</label>
                @include('owner.settings.partials.currency-select', [
                    'fieldName' => 'default_currency',
                    'fieldValue' => $settings['default_currency'],
                    'required' => true,
                ])
            </div>
        </div>
    </div>

    <hr>
    <h5>Tenant Domains</h5>
    <p>Each church gets a subdomain: <code>{church-slug}.{{ $baseDomain }}</code></p>
    <p class="text-muted">Change base domain via <code>TENANT_BASE_DOMAIN</code> in <code>.env</code> (current: <strong>{{ $baseDomain }}</strong>).</p>
    <p class="text-muted">Until wildcard DNS is ready, church links use the main domain with <code>?church=slug</code>. Set <code>TENANT_USE_SUBDOMAIN_URLS=true</code> after DNS is configured.</p>

    <button type="submit" class="btn btn-primary mt-2"><i class="fa fa-save"></i> Save Church Settings</button>
</form>
