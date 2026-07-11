@php
    $departments = $departments ?? collect();
    $leadershipPositions = $leadershipPositions ?? [];
    $assignmentRules = old('department_assignment_rules', $settings['department_assignment_rules'] ?? []);
    if (! is_array($assignmentRules) || $assignmentRules === []) {
        $assignmentRules = [[
            'department_id' => '',
            'genders' => [],
            'min_age' => '',
            'max_age' => '',
            'leadership_positions' => [],
        ]];
    }
@endphp

<form method="POST" action="{{ route('church.system.settings.update', 'membership') }}" id="membership-settings-form">
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

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="kipaimara_registration_enabled" value="1"
                       @checked(old('kipaimara_registration_enabled', $settings['kipaimara_registration_enabled'] ?? false))>
                <span class="label-text">Allow Kipaimara (confirmation) details during member registration</span>
            </label>
        </div>
        <small class="form-text text-muted">When enabled, registration shows Kipaimara fields similar to baptism. The officiant field is labeled Bishop (not Minister).</small>
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="children_education_details_enabled" value="1"
                       @checked(old('children_education_details_enabled', $settings['children_education_details_enabled'] ?? false))>
                <span class="label-text">Collect full education details for children</span>
            </label>
        </div>
        <small class="form-text text-muted">When enabled, child registration asks if the child is a student, then level (Nursery–University) and school location (region, district, optional ward/street).</small>
    </div>

    <hr class="my-4">

    <h5 class="mb-2">Automatic department assignment</h5>
    <div class="alert alert-light border mb-3">
        <p class="mb-2">Build one rule per department. Each rule can use <strong>gender</strong>, <strong>age</strong>, and/or <strong>leadership</strong>. If several are set, the person must match all of them.</p>
        <ul class="mb-0 pl-3">
            <li><strong>Women department</strong> — Gender: Female only</li>
            <li><strong>Idara ya watoto</strong> — Ages 0–12 (applies to children list + members in that age)</li>
            <li><strong>Idara ya vijana</strong> — Ages 13–40</li>
            <li><strong>Elders department</strong> — Leadership: Church Elder</li>
            <li><strong>Female youth</strong> — Gender: Female + ages 13–40</li>
        </ul>
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="department_assignment_enabled" value="1" id="department_assignment_enabled"
                       @checked(old('department_assignment_enabled', $settings['department_assignment_enabled'] ?? false))>
                <span class="label-text">Enable automatic department assignment for this church</span>
            </label>
        </div>
        @error('department_assignment_enabled')<div class="text-danger small">{{ $message }}</div>@enderror
        @error('department_assignment_rules')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div id="department-assignment-rules">
        <div id="department-assignment-rule-list">
            @foreach($assignmentRules as $index => $rule)
                @include('church.system.settings.partials.department-assignment-rule', [
                    'index' => $index,
                    'rule' => $rule,
                    'departments' => $departments,
                    'leadershipPositions' => $leadershipPositions,
                ])
            @endforeach
        </div>

        <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="add-department-assignment-rule">
            <i class="fa fa-plus"></i> Add rule
        </button>

        @if($departments->isEmpty())
            <div class="alert alert-warning">
                No active departments found.
                <a href="{{ route('church.departments.create') }}">Create a department</a> first, then return here to add rules.
            </div>
        @endif

        <div class="form-group">
            <div class="animated-checkbox">
                <label>
                    <input type="checkbox" name="sync_existing_members_on_save" value="1"
                           @checked(old('sync_existing_members_on_save'))>
                    <span class="label-text">After saving, sync existing members and children into matching departments</span>
                </label>
            </div>
            <small class="form-text text-muted">
                Checks everyone against these rules. New matches are added.
                People who no longer match age-based child departments are removed.
                Auto-assigned memberships that no longer match (including gender/leadership) are also removed.
            </small>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center" style="gap: 0.5rem;">
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Membership Settings</button>
    </div>
</form>

@if(! empty($settings['department_assignment_enabled']) && ! empty($settings['department_assignment_rules']))
    <form method="POST" action="{{ route('church.system.settings.membership.sync-departments') }}" class="mt-3"
          onsubmit="return confirm('Sync all existing active members and children using the already saved rules?');">
        @csrf
        <button type="submit" class="btn btn-outline-secondary">
            <i class="fa fa-refresh"></i> Sync existing members now
        </button>
        <small class="text-muted d-block mt-1">
            Uses rules already saved in the database. Save first if you just changed the form above.
        </small>
    </form>
@endif

<template id="department-assignment-rule-template">
    @include('church.system.settings.partials.department-assignment-rule', [
        'index' => '__INDEX__',
        'rule' => [
            'department_id' => '',
            'genders' => [],
            'min_age' => '',
            'max_age' => '',
            'leadership_positions' => [],
        ],
        'departments' => $departments,
        'leadershipPositions' => $leadershipPositions,
    ])
</template>

@push('scripts')
<script>
(function () {
    var toggle = document.getElementById('department_assignment_enabled');
    var fields = document.getElementById('department-assignment-rules');
    var list = document.getElementById('department-assignment-rule-list');
    var addBtn = document.getElementById('add-department-assignment-rule');
    var template = document.getElementById('department-assignment-rule-template');
    if (!toggle || !fields || !list || !addBtn || !template) return;

    function setInputsDisabled(disabled) {
        fields.querySelectorAll('input, select, button').forEach(function (el) {
            if (el === addBtn) {
                el.disabled = disabled;
                return;
            }
            if (el.closest && el.closest('#department-assignment-rule-list')) {
                el.disabled = disabled;
            }
            if (el.name === 'sync_existing_members_on_save') {
                el.disabled = disabled;
            }
        });
        addBtn.disabled = disabled;
    }

    function syncEnabled() {
        var enabled = toggle.checked;
        fields.style.opacity = enabled ? '1' : '0.55';
        setInputsDisabled(!enabled);
    }

    function nextIndex() {
        return list.querySelectorAll('.department-assignment-rule').length;
    }

    function updateRuleSummary(row) {
        var summary = row.querySelector('[data-rule-summary]');
        if (!summary) return;

        var deptSelect = row.querySelector('.rule-department');
        var deptName = '';
        if (deptSelect && deptSelect.selectedIndex >= 0) {
            deptName = (deptSelect.options[deptSelect.selectedIndex].text || '').trim();
            if (!deptSelect.value) deptName = '';
        }

        var genders = [];
        row.querySelectorAll('.rule-gender:checked').forEach(function (el) {
            genders.push(el.value === 'female' ? 'Female' : 'Male');
        });

        var minAge = (row.querySelector('.rule-min-age') || {}).value || '';
        var maxAge = (row.querySelector('.rule-max-age') || {}).value || '';
        var positions = [];
        row.querySelectorAll('.rule-leadership:checked').forEach(function (el) {
            var label = el.closest('label');
            var text = label ? (label.querySelector('.label-text') || {}).textContent : el.value;
            if (text) positions.push(String(text).trim());
        });

        var parts = [];
        if (deptName) parts.push(deptName);
        if (genders.length === 1) parts.push(genders[0] + ' only');
        else if (genders.length > 1) parts.push(genders.join(' & '));
        if (minAge !== '' && maxAge !== '') parts.push('Ages ' + minAge + '–' + maxAge);
        else if (minAge !== '' || maxAge !== '') parts.push('Age incomplete');
        if (positions.length) parts.push(positions.join(', '));

        summary.textContent = parts.length ? parts.join(' — ') : 'Choose a department and at least one requirement';
    }

    function updateAllSummaries() {
        list.querySelectorAll('.department-assignment-rule').forEach(updateRuleSummary);
    }

    function reindex() {
        list.querySelectorAll('.department-assignment-rule').forEach(function (row, index) {
            row.setAttribute('data-index', String(index));
            var title = row.querySelector('.card-header strong');
            if (title) title.textContent = 'Rule #' + (index + 1);
            row.querySelectorAll('[name]').forEach(function (input) {
                input.name = input.name.replace(/department_assignment_rules\[\d+\]/, 'department_assignment_rules[' + index + ']');
                input.name = input.name.replace(/department_assignment_rules\[__INDEX__\]/, 'department_assignment_rules[' + index + ']');
            });
            updateRuleSummary(row);
        });
    }

    addBtn.addEventListener('click', function () {
        var html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex()));
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        var row = wrapper.firstElementChild;
        list.appendChild(row);
        reindex();
        syncEnabled();
    });

    list.addEventListener('click', function (event) {
        var btn = event.target.closest('[data-remove-rule]');
        if (!btn) return;
        var row = btn.closest('.department-assignment-rule');
        if (!row) return;
        if (list.querySelectorAll('.department-assignment-rule').length <= 1) {
            row.querySelectorAll('input, select').forEach(function (el) {
                if (el.type === 'checkbox') {
                    el.checked = false;
                } else {
                    el.value = '';
                }
            });
            updateRuleSummary(row);
            return;
        }
        row.remove();
        reindex();
    });

    list.addEventListener('change', function (event) {
        var row = event.target.closest('.department-assignment-rule');
        if (row) updateRuleSummary(row);
    });

    list.addEventListener('input', function (event) {
        var row = event.target.closest('.department-assignment-rule');
        if (row) updateRuleSummary(row);
    });

    toggle.addEventListener('change', syncEnabled);
    syncEnabled();
    updateAllSummaries();

    var form = document.getElementById('membership-settings-form');
    if (form) {
        form.addEventListener('submit', function () {
            // Disabled inputs are not posted — re-enable so saved rules are preserved when the feature is off.
            setInputsDisabled(false);
        });
    }
})();
</script>
@endpush
