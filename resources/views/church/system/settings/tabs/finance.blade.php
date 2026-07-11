@php
    $months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];
    $customOfferingTypes = old('custom_offering_types', $settings['custom_offering_types'] ?? []);
    if (! is_array($customOfferingTypes)) {
        $customOfferingTypes = [];
    }
    $customOfferingTypes = array_values($customOfferingTypes);
    if ($customOfferingTypes === []) {
        $customOfferingTypes = [''];
    }
@endphp

<form method="POST" action="{{ route('church.system.settings.update', 'finance') }}">
    @csrf
    @method('PUT')

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="finance_approval_required" value="1"
                       @checked(old('finance_approval_required', $settings['finance_approval_required']))>
                <span class="label-text">{{ __('pages.church_settings_finance.approval_required') }}</span>
            </label>
        </div>
        <small class="form-text text-muted">{{ __('pages.church_settings_finance.approval_required_hint') }}</small>
    </div>

    <div class="form-group">
        <label>{{ __('pages.church_settings_finance.fiscal_year') }} <span class="text-danger">*</span></label>
        <select name="fiscal_year_start_month" class="form-control" required>
            @foreach($months as $num => $name)
                <option value="{{ $num }}" @selected((int) old('fiscal_year_start_month', $settings['fiscal_year_start_month']) === $num)>{{ $name }}</option>
            @endforeach
        </select>
        <small class="form-text text-muted">{{ __('pages.church_settings_finance.fiscal_year_hint') }}</small>
    </div>

    <hr class="my-3">
    <h5 class="mb-2">{{ __('pages.church_settings_finance.offering_types_heading') }}</h5>
    <p class="text-muted small mb-3">{{ __('pages.church_settings_finance.offering_types_help') }}</p>

    <div id="customOfferingTypesList">
        @foreach($customOfferingTypes as $index => $typeLabel)
            <div class="form-group custom-offering-type-row">
                <div class="input-group">
                    <input type="text"
                           name="custom_offering_types[]"
                           class="form-control"
                           value="{{ $typeLabel }}"
                           maxlength="100"
                           placeholder="{{ __('pages.church_settings_finance.offering_type_placeholder') }}">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-danger js-remove-offering-type" title="{{ __('common.delete') }}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="addOfferingTypeBtn">
        <i class="fa fa-plus"></i> {{ __('pages.church_settings_finance.add_offering_type') }}
    </button>
    @error('custom_offering_types')
        <div class="text-danger small mb-3">{{ $message }}</div>
    @enderror
    @error('custom_offering_types.*')
        <div class="text-danger small mb-3">{{ $message }}</div>
    @enderror

    <div>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.church_settings_finance.save') }}</button>
    </div>
</form>

@push('scripts')
<script>
(function () {
    var list = document.getElementById('customOfferingTypesList');
    var addBtn = document.getElementById('addOfferingTypeBtn');
    var placeholder = @json(__('pages.church_settings_finance.offering_type_placeholder'));
    var removeTitle = @json(__('common.delete'));
    if (!list || !addBtn) return;

    function bindRemove(button) {
        button.addEventListener('click', function () {
            var rows = list.querySelectorAll('.custom-offering-type-row');
            var row = button.closest('.custom-offering-type-row');
            if (!row) return;
            if (rows.length <= 1) {
                var input = row.querySelector('input');
                if (input) input.value = '';
                return;
            }
            row.remove();
        });
    }

    list.querySelectorAll('.js-remove-offering-type').forEach(bindRemove);

    addBtn.addEventListener('click', function () {
        var row = document.createElement('div');
        row.className = 'form-group custom-offering-type-row';

        var group = document.createElement('div');
        group.className = 'input-group';

        var input = document.createElement('input');
        input.type = 'text';
        input.name = 'custom_offering_types[]';
        input.className = 'form-control';
        input.maxLength = 100;
        input.placeholder = placeholder;

        var append = document.createElement('div');
        append.className = 'input-group-append';

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-outline-danger js-remove-offering-type';
        removeBtn.title = removeTitle;
        removeBtn.innerHTML = '<i class="fa fa-trash"></i>';

        append.appendChild(removeBtn);
        group.appendChild(input);
        group.appendChild(append);
        row.appendChild(group);
        list.appendChild(row);
        bindRemove(removeBtn);
        input.focus();
    });
})();
</script>
@endpush
