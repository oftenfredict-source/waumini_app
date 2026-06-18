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
    <h5 class="mb-3">Church Profile</h5>    <div class="row">
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
                    <option value="en" @selected(old('locale', $settings['locale']) === 'en')>English</option>
                    <option value="sw" @selected(old('locale', $settings['locale']) === 'sw')>Swahili</option>
                </select>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save General Settings</button>
</form>
