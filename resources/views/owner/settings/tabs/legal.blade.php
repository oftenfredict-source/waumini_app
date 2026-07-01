<form method="POST" action="{{ route('owner.settings.legal') }}">
    @csrf
    @method('PUT')
    <h4 class="mb-3">{{ __('owner.set.terms') }}</h4>
    <p class="text-muted">{{ __('owner.set.terms_help') }}</p>
    <div class="form-group">
        <label>{{ __('owner.set.terms_content') }}</label>
        <textarea name="terms_and_conditions" class="form-control" rows="18" required>{{ old('terms_and_conditions', $settings['terms_and_conditions']) }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('owner.set.save_legal') }}</button>
</form>
