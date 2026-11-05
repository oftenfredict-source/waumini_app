<form method="POST" action="{{ route('owner.settings.legal') }}">
    @csrf
    @method('PUT')

    <h2 class="h5 mb-1">{{ __('owner.set.terms') }}</h2>
    <p class="text-muted small mb-4">{{ __('owner.set.terms_help') }}</p>

    <div class="mb-3">
        <label class="form-label" for="terms_and_conditions">{{ __('owner.set.terms_content') }}</label>
        <textarea id="terms_and_conditions" name="terms_and_conditions" class="form-control" rows="18" required>{{ old('terms_and_conditions', $settings['terms_and_conditions']) }}</textarea>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save me-1"></i> {{ __('owner.set.save_legal') }}
    </button>
</form>
