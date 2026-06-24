<form method="POST" action="{{ route('church.system.settings.update', 'membership') }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Child Maximum Age <span class="text-danger">*</span></label>
                <input type="number" name="child_max_age" class="form-control @error('child_max_age') is-invalid @enderror"
                       min="1" max="30" value="{{ old('child_max_age', $settings['child_max_age']) }}" required>
                <small class="form-text text-muted">Maximum age before a dependant is treated as an independent member.</small>
                @error('child_max_age')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Member ID Prefix <span class="text-danger">*</span></label>
                <input type="text" name="member_id_prefix" class="form-control @error('member_id_prefix') is-invalid @enderror"
                       maxlength="10" value="{{ old('member_id_prefix', $settings['member_id_prefix']) }}" required>
                <small class="form-text text-muted">Used in member numbers, e.g. {{ strtoupper(old('member_id_prefix', $settings['member_id_prefix'])) }}-{{ now()->format('Y') }}-0001</small>
                @error('member_id_prefix')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="auto_generate_member_id" value="1"
                       @checked(old('auto_generate_member_id', $settings['auto_generate_member_id']))>
                <span class="label-text">Automatically generate member IDs for new members</span>
            </label>
        </div>
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="require_member_phone" value="1"
                       @checked(old('require_member_phone', $settings['require_member_phone']))>
                <span class="label-text">Require phone number when registering members</span>
            </label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Membership Settings</button>
</form>
