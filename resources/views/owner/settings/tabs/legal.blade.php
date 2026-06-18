<form method="POST" action="{{ route('owner.settings.legal') }}">
    @csrf
    @method('PUT')
    <h4 class="mb-3">Terms &amp; Conditions</h4>
    <p class="text-muted">This content is shown to church administrators and staff on the Terms &amp; Conditions page. HTML is supported.</p>
    <div class="form-group">
        <label>Terms &amp; Conditions Content</label>
        <textarea name="terms_and_conditions" class="form-control" rows="18" required>{{ old('terms_and_conditions', $settings['terms_and_conditions']) }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Legal Settings</button>
</form>
