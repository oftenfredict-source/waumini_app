<form method="POST" action="{{ route('owner.settings.system') }}">
    @csrf
    @method('PUT')

    <h2 class="h5 mb-3">{{ __('owner.set.system_control') }}</h2>

    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="maintenance_mode" name="maintenance_mode" value="1" @checked(old('maintenance_mode', $settings['maintenance_mode']))>
        <label class="form-check-label" for="maintenance_mode">{{ __('owner.set.maintenance_mode') }}</label>
    </div>

    <div class="mb-4">
        <label class="form-label" for="maintenance_message">{{ __('owner.set.maintenance_message') }}</label>
        <textarea id="maintenance_message" name="maintenance_message" class="form-control" rows="3" placeholder="{{ __('owner.set.maintenance_placeholder') }}">{{ old('maintenance_message', $settings['maintenance_message']) }}</textarea>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save me-1"></i> {{ __('owner.set.save_system') }}
    </button>
</form>
