<form method="POST" action="{{ route('owner.settings.general') }}">
    @csrf
    @method('PUT')
    <h4 class="mb-3">{{ __('owner.set.branding') }}</h4>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('owner.set.app_name') }}</label>
                <input type="text" name="app_name" class="form-control" value="{{ old('app_name', $settings['app_name']) }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('owner.set.support_email') }}</label>
                <input type="email" name="support_email" class="form-control" value="{{ old('support_email', $settings['support_email']) }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('owner.set.support_phone') }}</label>
                <input type="text" name="support_phone" class="form-control" value="{{ old('support_phone', $settings['support_phone']) }}" placeholder="{{ __('owner.set.phone_placeholder') }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('owner.set.email_from_name') }}</label>
                <input type="text" name="email_from_name" class="form-control" value="{{ old('email_from_name', $settings['email_from_name']) }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('owner.set.email_from_address') }}</label>
                <input type="email" name="email_from_address" class="form-control" value="{{ old('email_from_address', $settings['email_from_address']) }}">
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('owner.set.save_general') }}</button>
</form>
