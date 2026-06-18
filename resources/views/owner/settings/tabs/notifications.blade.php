<form method="POST" action="{{ route('owner.settings.notifications') }}">
    @csrf
    @method('PUT')
    <h4 class="mb-3">Platform Notifications</h4>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <div class="animated-checkbox">
                    <label>
                        <input type="checkbox" name="email_notifications" value="1" @checked(old('email_notifications', $settings['email_notifications']))>
                        <span class="label-text">Enable email notifications</span>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <div class="animated-checkbox">
                    <label>
                        <input type="checkbox" name="sms_enabled" value="1" @checked(old('sms_enabled', $settings['sms_enabled']))>
                        <span class="label-text">Enable SMS notifications (platform-wide)</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Subscription expiry reminders (days before)</label>
                <input type="text" name="expiry_reminder_days" class="form-control" value="{{ old('expiry_reminder_days', $settings['expiry_reminder_days']) }}" placeholder="7,3,1">
                <small class="text-muted">Comma-separated days before expiry to send reminders to churches.</small>
            </div>
        </div>
    </div>

    <hr class="my-4">
    <h4 class="mb-3">SMS Gateway (messaging-service.co.tz)</h4>

    @if($settings['sms_configured'])
        <div class="alert alert-success py-2">
            <i class="fa fa-check-circle"></i> Gateway credentials are configured.
        </div>
    @else
        <div class="alert alert-warning py-2">
            <i class="fa fa-exclamation-triangle"></i> Enter SMS gateway credentials below, or set <code>SMS_*</code> variables in <code>.env</code>.
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>API URL</label>
                <input type="url" name="sms_api_url" class="form-control"
                       value="{{ old('sms_api_url', $settings['sms_api_url']) }}"
                       placeholder="https://messaging-service.co.tz/link/sms/v1/text/single">
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="sms_username" class="form-control"
                       value="{{ old('sms_username', $settings['sms_username']) }}" autocomplete="off">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="sms_password" class="form-control"
                       placeholder="{{ $settings['sms_configured'] ? 'Leave blank to keep current password' : 'Enter API password' }}"
                       autocomplete="new-password">
            </div>
            <div class="form-group">
                <label>Sender ID (from)</label>
                <input type="text" name="sms_sender_id" class="form-control"
                       value="{{ old('sms_sender_id', $settings['sms_sender_id']) }}" maxlength="20"
                       placeholder="WauminiLnk">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Notification Settings</button>
</form>

<hr class="my-4">

<form method="POST" action="{{ route('owner.settings.sms-test') }}" class="mt-3">
    @csrf
    <h5 class="mb-3">Send Test SMS</h5>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Phone number</label>
                <input type="text" name="test_phone" class="form-control" required
                       value="{{ old('test_phone', '255614863345') }}" placeholder="255614863345">
                <small class="text-muted">Format: 255XXXXXXXXX (no + sign)</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Message</label>
                <input type="text" name="test_message" class="form-control" maxlength="160"
                       value="{{ old('test_message', 'HABARI') }}" placeholder="HABARI">
            </div>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <div class="form-group">
                <button type="submit" class="btn btn-outline-primary btn-block">
                    <i class="fa fa-paper-plane"></i> Send Test
                </button>
            </div>
        </div>
    </div>
</form>
