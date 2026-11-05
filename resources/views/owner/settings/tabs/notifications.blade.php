<form method="POST" action="{{ route('owner.settings.notifications') }}">
    @csrf
    @method('PUT')

    <h2 class="h5 mb-3">{{ __('owner.set.notifications') }}</h2>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="email_notifications" name="email_notifications" value="1" @checked(old('email_notifications', $settings['email_notifications']))>
                <label class="form-check-label" for="email_notifications">{{ __('owner.set.enable_email') }}</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="sms_enabled" name="sms_enabled" value="1" @checked(old('sms_enabled', $settings['sms_enabled']))>
                <label class="form-check-label" for="sms_enabled">{{ __('owner.set.enable_sms') }}</label>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="expiry_reminder_days">{{ __('owner.set.expiry_reminders') }}</label>
            <input type="text" id="expiry_reminder_days" name="expiry_reminder_days" class="form-control" value="{{ old('expiry_reminder_days', $settings['expiry_reminder_days']) }}" placeholder="7,3,1">
            <div class="form-text">{{ __('owner.set.reminders_help') }}</div>
        </div>
    </div>

    <hr class="my-4">

    <h2 class="h5 mb-3">{{ __('owner.set.sms_gateway') }}</h2>

    @if($settings['sms_configured'])
        <div class="alert border-0 mb-3" style="background:rgba(22,163,74,.1);color:#166534;">
            <i class="bi bi-check-circle me-1"></i> {{ __('owner.set.credentials_configured') }}
        </div>
    @else
        <div class="alert border-0 mb-3" style="background:rgba(217,119,6,.12);color:#92400e;">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ __('owner.set.credentials_env') }}
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="sms_api_url">{{ __('owner.set.api_url') }}</label>
            <input type="url" id="sms_api_url" name="sms_api_url" class="form-control"
                   value="{{ old('sms_api_url', $settings['sms_api_url']) }}"
                   placeholder="https://messaging-service.co.tz/link/sms/v1/text/single">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="sms_password">{{ __('owner.set.password') }}</label>
            <input type="password" id="sms_password" name="sms_password" class="form-control"
                   placeholder="{{ $settings['sms_configured'] ? __('owner.set.keep_password') : __('owner.set.enter_password') }}"
                   autocomplete="new-password">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="sms_username">{{ __('owner.set.username') }}</label>
            <input type="text" id="sms_username" name="sms_username" class="form-control"
                   value="{{ old('sms_username', $settings['sms_username']) }}" autocomplete="off">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="sms_sender_id">{{ __('owner.set.sender_id') }}</label>
            <input type="text" id="sms_sender_id" name="sms_sender_id" class="form-control"
                   value="{{ old('sms_sender_id', $settings['sms_sender_id']) }}" maxlength="20"
                   placeholder="{{ __('owner.set.sender_placeholder') }}">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> {{ __('owner.set.save_notifications') }}
        </button>
    </div>
</form>

<hr class="my-4">

<form method="POST" action="{{ route('owner.settings.sms-test') }}">
    @csrf
    <h2 class="h5 mb-3">{{ __('owner.set.send_test_sms') }}</h2>
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label" for="test_phone">{{ __('owner.set.phone_number') }}</label>
            <input type="text" id="test_phone" name="test_phone" class="form-control" required
                   value="{{ old('test_phone', '255614863345') }}" placeholder="255614863345">
            <div class="form-text">{{ __('owner.set.phone_format') }}</div>
        </div>
        <div class="col-md-5">
            <label class="form-label" for="test_message">{{ __('owner.set.message') }}</label>
            <input type="text" id="test_message" name="test_message" class="form-control" maxlength="160"
                   value="{{ old('test_message', 'HABARI') }}" placeholder="HABARI">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-outline-primary w-100">
                <i class="bi bi-send me-1"></i> {{ __('owner.set.send_test') }}
            </button>
        </div>
    </div>
</form>
