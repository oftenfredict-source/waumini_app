<form method="POST" action="{{ route('church.system.settings.update', 'notifications') }}">
    @csrf
    @method('PUT')

    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i>
        {{ __('pages.church_settings_notifications.platform_info') }}
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="email_notifications" value="1"
                       @checked(old('email_notifications', $settings['email_notifications']))>
                <span class="label-text">{{ __('pages.church_settings_notifications.enable_email') }}</span>
            </label>
        </div>
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="sms_enabled" value="1"
                       @checked(old('sms_enabled', $settings['sms_enabled']))>
                <span class="label-text">{{ __('pages.church_settings_notifications.enable_sms') }}</span>
            </label>
        </div>
    </div>

    <hr class="my-3">
    <h5 class="mb-3">{{ __('pages.church_settings_notifications.sender_id_heading') }}</h5>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="use_custom_sender_id" value="1" id="use_custom_sender_id"
                       @checked(old('use_custom_sender_id', $settings['use_custom_sender_id']))>
                <span class="label-text">{{ __('pages.church_settings_notifications.use_custom_sender_id') }}</span>
            </label>
        </div>
        <small class="text-muted d-block mt-1">
            {{ __('pages.church_settings_notifications.default_sender_id', ['sender' => $platformSenderId]) }}
        </small>
    </div>

    <div class="form-group" id="custom_sender_id_group">
        <label for="sms_sender_id">{{ __('pages.church_settings_notifications.sender_id_label') }}</label>
        <input type="text" name="sms_sender_id" id="sms_sender_id" class="form-control @error('sms_sender_id') is-invalid @enderror"
               value="{{ old('sms_sender_id', $settings['sms_sender_id']) }}" maxlength="20"
               placeholder="{{ __('pages.church_settings_notifications.sender_id_placeholder') }}">
        <small class="text-muted">{{ __('pages.church_settings_notifications.sender_id_help') }}</small>
        @error('sms_sender_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <hr class="my-3">
    <h5 class="mb-3">{{ __('pages.church_settings_notifications.sms_types_heading') }}</h5>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="announcement_sms" value="1"
                       @checked(old('announcement_sms', $settings['announcement_sms']))>
                <span class="label-text">{{ __('pages.church_settings_notifications.announcement_sms') }}</span>
            </label>
        </div>
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="member_credentials_sms" value="1"
                       @checked(old('member_credentials_sms', $settings['member_credentials_sms']))>
                <span class="label-text">{{ __('pages.church_settings_notifications.member_credentials_sms') }}</span>
            </label>
        </div>
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="password_reset_sms" value="1"
                       @checked(old('password_reset_sms', $settings['password_reset_sms'] ?? true))>
                <span class="label-text">{{ __('pages.church_settings_notifications.password_reset_sms') }}</span>
            </label>
        </div>
        <small class="text-muted">{{ __('pages.church_settings_notifications.password_reset_hint') }}</small>
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="finance_approval_sms" value="1"
                       @checked(old('finance_approval_sms', $settings['finance_approval_sms'] ?? true))>
                <span class="label-text">{{ __('pages.church_settings_notifications.finance_approval_sms') }}</span>
            </label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.church_settings_notifications.save') }}</button>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('use_custom_sender_id');
    const group = document.getElementById('custom_sender_id_group');

    function syncSenderIdField() {
        if (!toggle || !group) {
            return;
        }

        group.style.display = toggle.checked ? '' : 'none';
    }

    toggle?.addEventListener('change', syncSenderIdField);
    syncSenderIdField();
});
</script>
@endpush
