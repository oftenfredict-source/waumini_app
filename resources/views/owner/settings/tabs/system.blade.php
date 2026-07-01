<form method="POST" action="{{ route('owner.settings.system') }}">
    @csrf
    @method('PUT')
    <h4 class="mb-3">{{ __('owner.set.system_control') }}</h4>
    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="maintenance_mode" value="1" @checked(old('maintenance_mode', $settings['maintenance_mode']))>
                <span class="label-text">{{ __('owner.set.maintenance_mode') }}</span>
            </label>
        </div>
    </div>
    <div class="form-group">
        <label>{{ __('owner.set.maintenance_message') }}</label>
        <textarea name="maintenance_message" class="form-control" rows="3" placeholder="{{ __('owner.set.maintenance_placeholder') }}">{{ old('maintenance_message', $settings['maintenance_message']) }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('owner.set.save_system') }}</button>
</form>
