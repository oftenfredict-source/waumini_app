<form method="POST" action="{{ route('church.system.settings.update', 'general') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <h5 class="mb-3">Church Logo</h5>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                @if($settings['church_logo_url'])
                    <img src="{{ $settings['church_logo_url'] }}" alt="{{ $settings['church_name'] }} logo"
                         style="max-height: 80px; max-width: 180px; object-fit: contain; border: 1px solid #eee; padding: 8px; border-radius: 6px; background: #fff;">
                @else
                    <div class="text-muted border rounded p-3" style="min-width: 180px; text-align: center;">
                        <i class="fa fa-image fa-2x mb-2 d-block"></i>
                        No logo uploaded
                    </div>
                @endif
                <div>
                    <div class="form-group mb-2">
                        <label>Upload Logo</label>
                        <input type="file" name="logo" class="form-control-file @error('logo') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                        @error('logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="text-muted d-block mt-1">PNG, JPG, or WEBP. Max 2MB. Shown on certificates and church pages.</small>
                    </div>
                    @if($settings['church_logo_url'])
                        <div class="form-check">
                            <input type="checkbox" name="remove_logo" value="1" class="form-check-input" id="remove_logo" @checked(old('remove_logo'))>
                            <label class="form-check-label" for="remove_logo">Remove current logo</label>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <hr>
    @php
        $selectedBrand = old('brand_color', $settings['brand_color'] ?? '#940000');
        $brandPresets = config('church_settings.brand_presets', []);
    @endphp
    <h5 class="mb-2">Theme Color</h5>
    <p class="text-muted mb-3">Choose a brand color for this church. Header, sidebar, buttons, and links will use it.</p>
    <div class="row mb-4 align-items-end">
        <div class="col-md-4 col-lg-3">
            <div class="form-group mb-0">
                <label for="brand_color">Primary color <span class="text-danger">*</span></label>
                <div class="d-flex align-items-center" style="gap: 0.75rem;">
                    <input type="color" id="brand_color_picker" value="{{ $selectedBrand }}"
                           style="width: 3rem; height: 2.5rem; padding: 0; border: 1px solid #ced4da; border-radius: 0.35rem; background: #fff; cursor: pointer;"
                           aria-label="Pick brand color">
                    <input type="text" id="brand_color" name="brand_color"
                           class="form-control @error('brand_color') is-invalid @enderror"
                           value="{{ $selectedBrand }}" pattern="^#?([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$" maxlength="7" required>
                </div>
                @error('brand_color')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-8 col-lg-9">
            <label class="d-block mb-2">Quick presets</label>
            <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                @foreach($brandPresets as $hex => $label)
                    <button type="button"
                            class="btn btn-sm brand-color-preset {{ strcasecmp($selectedBrand, $hex) === 0 ? 'active' : '' }}"
                            data-brand-color="{{ $hex }}"
                            title="{{ $label }}"
                            style="min-width: 2.25rem; height: 2.25rem; border-radius: 999px; background: {{ $hex }}; border: 2px solid {{ strcasecmp($selectedBrand, $hex) === 0 ? '#111' : 'transparent' }}; box-shadow: inset 0 0 0 1px rgba(255,255,255,.35);">
                        <span class="sr-only">{{ $label }}</span>
                    </button>
                @endforeach
            </div>
            <div class="mt-3 d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
                <span class="badge text-white" style="background: {{ $selectedBrand }};" id="brand_color_preview_badge">Preview</span>
                <small class="text-muted">Applies after you save general settings.</small>
            </div>
        </div>
    </div>

    <hr>
    <h5 class="mb-3">Church Profile</h5>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Church Name <span class="text-danger">*</span></label>
                <input type="text" name="church_name" class="form-control @error('church_name') is-invalid @enderror"
                       value="{{ old('church_name', $settings['church_name']) }}" required>
                @error('church_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Email <span class="text-danger">*</span></label>
                <input type="email" name="church_email" class="form-control @error('church_email') is-invalid @enderror"
                       value="{{ old('church_email', $settings['church_email']) }}" required>
                @error('church_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="church_phone" class="form-control"
                       value="{{ old('church_phone', $settings['church_phone']) }}" placeholder="+255 ...">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Pastor / Leader Name</label>
                <input type="text" name="pastor_name" class="form-control"
                       value="{{ old('pastor_name', $settings['pastor_name']) }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Denomination</label>
                <input type="text" name="denomination" class="form-control"
                       value="{{ old('denomination', $settings['denomination']) }}">
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>Address</label>
                <textarea name="church_address" class="form-control" rows="2">{{ old('church_address', $settings['church_address']) }}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>City</label>
                <input type="text" name="church_city" class="form-control"
                       value="{{ old('church_city', $settings['church_city']) }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Country</label>
                <input type="text" name="church_country" class="form-control"
                       value="{{ old('church_country', $settings['church_country']) }}">
            </div>
        </div>
    </div>

    <hr>
    <h5 class="mb-3">Regional Preferences</h5>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Timezone <span class="text-danger">*</span></label>
                <select name="timezone" class="form-control" required>
                    @foreach(config('church_settings.timezones') as $value => $label)
                        <option value="{{ $value }}" @selected(old('timezone', $settings['timezone']) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Currency <span class="text-danger">*</span></label>
                <select name="currency" class="form-control" required>
                    @foreach(config('currencies') as $code => $label)
                        <option value="{{ $code }}" @selected(old('currency', $settings['currency']) === $code)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Date Format <span class="text-danger">*</span></label>
                <select name="date_format" class="form-control" required>
                    @foreach(config('church_settings.date_formats') as $value => $label)
                        <option value="{{ $value }}" @selected(old('date_format', $settings['date_format']) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Language / Locale</label>
                <select name="locale" class="form-control">
                    <option value="en" @selected(old('locale', $settings['locale']) === 'en')>{{ __('common.language') }}: English</option>
                    <option value="sw" @selected(old('locale', $settings['locale']) === 'sw')>{{ __('common.language') }}: Kiswahili</option>
                </select>
                <small class="text-muted">Default language for church users. Individuals can still switch language from the header.</small>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save General Settings</button>
</form>

<script>
(function () {
    var picker = document.getElementById('brand_color_picker');
    var input = document.getElementById('brand_color');
    var badge = document.getElementById('brand_color_preview_badge');
    var presets = document.querySelectorAll('.brand-color-preset');

    function normalize(value) {
        value = String(value || '').trim();
        if (!value) return '#940000';
        if (value.charAt(0) !== '#') value = '#' + value;
        if (/^#[A-Fa-f0-9]{3}$/.test(value)) {
            value = '#' + value[1] + value[1] + value[2] + value[2] + value[3] + value[3];
        }
        return value.toUpperCase();
    }

    function applyColor(value, syncPicker) {
        value = normalize(value);
        input.value = value;
        if (syncPicker !== false && picker) picker.value = value;
        if (badge) badge.style.background = value;
        presets.forEach(function (btn) {
            var active = normalize(btn.getAttribute('data-brand-color')) === value;
            btn.classList.toggle('active', active);
            btn.style.borderColor = active ? '#111' : 'transparent';
        });
    }

    if (picker) {
        picker.addEventListener('input', function () { applyColor(picker.value, false); });
    }
    if (input) {
        input.addEventListener('change', function () { applyColor(input.value); });
        input.addEventListener('blur', function () { applyColor(input.value); });
    }
    presets.forEach(function (btn) {
        btn.addEventListener('click', function () {
            applyColor(btn.getAttribute('data-brand-color'));
        });
    });
})();
</script>
