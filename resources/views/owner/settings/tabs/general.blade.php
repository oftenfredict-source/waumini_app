<form method="POST" action="{{ route('owner.settings.general') }}">
    @csrf
    @method('PUT')
    <h2 class="h5 mb-1">{{ __('owner.set.branding') }}</h2>
    <p class="text-muted small mb-4">{{ __('owner.set.subtitle') }}</p>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="app_name">{{ __('owner.set.app_name') }}</label>
            <input type="text" id="app_name" name="app_name" class="form-control" value="{{ old('app_name', $settings['app_name']) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="support_email">{{ __('owner.set.support_email') }}</label>
            <input type="email" id="support_email" name="support_email" class="form-control" value="{{ old('support_email', $settings['support_email']) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="support_phone">{{ __('owner.set.support_phone') }}</label>
            <input type="text" id="support_phone" name="support_phone" class="form-control" value="{{ old('support_phone', $settings['support_phone']) }}" placeholder="{{ __('owner.set.phone_placeholder') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="email_from_name">{{ __('owner.set.email_from_name') }}</label>
            <input type="text" id="email_from_name" name="email_from_name" class="form-control" value="{{ old('email_from_name', $settings['email_from_name']) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="email_from_address">{{ __('owner.set.email_from_address') }}</label>
            <input type="email" id="email_from_address" name="email_from_address" class="form-control" value="{{ old('email_from_address', $settings['email_from_address']) }}">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> {{ __('owner.set.save_general') }}
        </button>
    </div>
</form>
