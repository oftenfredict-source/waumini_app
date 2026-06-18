<form method="POST" action="{{ route('church.system.settings.update', 'notifications') }}">
    @csrf
    @method('PUT')

    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i>
        SMS is configured at the platform level. Enable SMS below and ensure your subscription package includes the SMS feature.
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="email_notifications" value="1"
                       @checked(old('email_notifications', $settings['email_notifications']))>
                <span class="label-text">Enable email notifications</span>
            </label>
        </div>
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="sms_enabled" value="1"
                       @checked(old('sms_enabled', $settings['sms_enabled']))>
                <span class="label-text">Enable SMS notifications</span>
            </label>
        </div>
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="announcement_sms" value="1"
                       @checked(old('announcement_sms', $settings['announcement_sms']))>
                <span class="label-text">Send SMS when publishing announcements</span>
            </label>
        </div>
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="member_credentials_sms" value="1"
                       @checked(old('member_credentials_sms', $settings['member_credentials_sms']))>
                <span class="label-text">Send SMS with login credentials when registering members</span>
            </label>
        </div>
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="password_reset_sms" value="1"
                       @checked(old('password_reset_sms', $settings['password_reset_sms'] ?? true))>
                <span class="label-text">Send SMS when resetting member or staff passwords</span>
            </label>
        </div>
        <small class="text-muted">Works when platform SMS is on and your package includes SMS — no need to enable general SMS above.</small>
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="finance_approval_sms" value="1"
                       @checked(old('finance_approval_sms', $settings['finance_approval_sms'] ?? true))>
                <span class="label-text">Send SMS when tithes, offerings, or pledge payments are approved</span>
            </label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Notification Settings</button>
</form>
