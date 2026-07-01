<form method="POST" action="{{ route('owner.settings.notifications') }}">
    @csrf
    @method('PUT')
    <h4 class="mb-3">{{ __('owner.set.notifications') }}</h4>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <div class="animated-checkbox">
                    <label>
                        <input type="checkbox" name="email_notifications" value="1" @checked(old('email_notifications', $settings['email_notifications']))>
                        <span class="label-text">{{ __('owner.set.enable_email') }}</span>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <div class="animated-checkbox">
                    <label>
                        <input type="checkbox" name="sms_enabled" value="1" @checked(old('sms_enabled', $settings['sms_enabled']))>
                        <span class="label-text">{{ __('owner.set.enable_sms') }}</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('owner.set.expiry_reminders') }}</label>
                <input type="text" name="expiry_reminder_days" class="form-control" value="{{ old('expiry_reminder_days', $settings['expiry_reminder_days']) }}" placeholder="7,3,1">
                <small class="text-muted">{{ __('owner.set.reminders_help') }}</small>
            </div>
        </div>
    </div>

    <hr class="my-4">
    <h4 class="mb-3">{{ __('owner.set.sms_gateway') }}</h4>

    @if($settings['sms_configured'])
        <div class="alert alert-success py-2">
            <i class="fa fa-check-circle"></i> {{ __('owner.set.credentials_configured') }}
        </div>
    @else
        <div class="alert alert-warning py-2">
            <i class="fa fa-exclamation-triangle"></i> {{ __('owner.set.credentials_env') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('owner.set.api_url') }}</label>
                <input type="url" name="sms_api_url" class="form-control"
                       value="{{ old('sms_api_url', $settings['sms_api_url']) }}"
                       placeholder="https://messaging-service.co.tz/link/sms/v1/text/single">
            </div>
            <div class="form-group">
                <label>{{ __('owner.set.username') }}</label>
                <input type="text" name="sms_username" class="form-control"
                       value="{{ old('sms_username', $settings['sms_username']) }}" autocomplete="off">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('owner.set.password') }}</label>
                <input type="password" name="sms_password" class="form-control"
                       placeholder="{{ $settings['sms_configured'] ? __('owner.set.keep_password') : __('owner.set.enter_password') }}"
                       autocomplete="new-password">
            </div>
            <div class="form-group">
                <label>{{ __('owner.set.sender_id') }}</label>
                <input type="text" name="sms_sender_id" class="form-control"
                       value="{{ old('sms_sender_id', $settings['sms_sender_id']) }}" maxlength="20"
                       placeholder="{{ __('owner.set.sender_placeholder') }}">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('owner.set.save_notifications') }}</button>
</form>

<hr class="my-4">

<form method="POST" action="{{ route('owner.settings.sms-test') }}" class="mt-3">
    @csrf
    <h5 class="mb-3">{{ __('owner.set.send_test_sms') }}</h5>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.phone_number') }}</label>
                <input type="text" name="test_phone" class="form-control" required
                       value="{{ old('test_phone', '255614863345') }}" placeholder="255614863345">
                <small class="text-muted">{{ __('owner.set.phone_format') }}</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('owner.set.message') }}</label>
                <input type="text" name="test_message" class="form-control" maxlength="160"
                       value="{{ old('test_message', 'HABARI') }}" placeholder="HABARI">
            </div>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <div class="form-group">
                <button type="submit" class="btn btn-outline-primary btn-block">
                    <i class="fa fa-paper-plane"></i> {{ __('owner.set.send_test') }}
                </button>
            </div>
        </div>
    </div>
</form>
