<form method="POST" action="{{ route('owner.settings.system') }}">
    @csrf
    @method('PUT')
    <h4 class="mb-3">System Control</h4>
    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="maintenance_mode" value="1" @checked(old('maintenance_mode', $settings['maintenance_mode']))>
                <span class="label-text"><strong>Maintenance Mode</strong> — block all church dashboards</span>
            </label>
        </div>
    </div>
    <div class="form-group">
        <label>Maintenance Message</label>
        <textarea name="maintenance_message" class="form-control" rows="3" placeholder="We are performing scheduled maintenance. Please check back soon.">{{ old('maintenance_message', $settings['maintenance_message']) }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save System Settings</button>
</form>
