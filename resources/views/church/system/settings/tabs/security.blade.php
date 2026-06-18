<form method="POST" action="{{ route('church.system.settings.update', 'security') }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Session Timeout (minutes) <span class="text-danger">*</span></label>
                <input type="number" name="session_timeout_minutes" class="form-control @error('session_timeout_minutes') is-invalid @enderror"
                       min="15" max="480" value="{{ old('session_timeout_minutes', $settings['session_timeout_minutes']) }}" required>
                <small class="form-text text-muted">Inactive users are logged out after this period (15–480 minutes).</small>
                @error('session_timeout_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Max Login Attempts <span class="text-danger">*</span></label>
                <input type="number" name="max_login_attempts" class="form-control @error('max_login_attempts') is-invalid @enderror"
                       min="3" max="20" value="{{ old('max_login_attempts', $settings['max_login_attempts']) }}" required>
                <small class="form-text text-muted">Failed attempts before a temporary lockout (enforcement coming soon).</small>
                @error('max_login_attempts')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="otp_login_enabled" value="1"
                       @checked(old('otp_login_enabled', $settings['otp_login_enabled'] ?? false))>
                <span class="label-text">Require SMS OTP verification after password sign-in</span>
            </label>
        </div>
        <small class="form-text text-muted">
            When enabled, users receive a 6-digit code on their registered phone after entering a valid password.
            Requires platform SMS, SMS package feature, and a phone number on each account.
        </small>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Security Settings</button>
</form>
