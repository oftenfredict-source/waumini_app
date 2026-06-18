@php
    $months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];
@endphp

<form method="POST" action="{{ route('church.system.settings.update', 'finance') }}">
    @csrf
    @method('PUT')

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="finance_approval_required" value="1"
                       @checked(old('finance_approval_required', $settings['finance_approval_required']))>
                <span class="label-text">Require approval for finance transactions</span>
            </label>
        </div>
        <small class="form-text text-muted">When enabled, tithes, offerings, expenses, and pledges may need approval before they are finalized.</small>
    </div>

    <div class="form-group">
        <label>Fiscal Year Start Month <span class="text-danger">*</span></label>
        <select name="fiscal_year_start_month" class="form-control" required>
            @foreach($months as $num => $name)
                <option value="{{ $num }}" @selected((int) old('fiscal_year_start_month', $settings['fiscal_year_start_month']) === $num)>{{ $name }}</option>
            @endforeach
        </select>
        <small class="form-text text-muted">Used for budget and financial reporting periods.</small>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Finance Settings</button>
</form>
