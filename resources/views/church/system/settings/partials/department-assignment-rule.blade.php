@php
    $rule = is_array($rule ?? null) ? $rule : [];
    $selectedPositions = array_map('strval', $rule['leadership_positions'] ?? []);
    $selectedGenders = array_map('strval', $rule['genders'] ?? []);
    $ruleNumber = is_numeric($index) ? ((int) $index + 1) : '';
@endphp

<div class="department-assignment-rule card mb-3" data-index="{{ $index }}">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <div>
            <strong>Rule @if($ruleNumber !== '')#{{ $ruleNumber }}@endif</strong>
            <div class="small text-muted rule-summary mt-1" data-rule-summary>—</div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-rule title="Remove rule">
            <i class="fa fa-trash"></i>
        </button>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label class="font-weight-bold">1. Department</label>
            <select name="department_assignment_rules[{{ $index }}][department_id]"
                    class="form-control rule-department @error('department_assignment_rules.'.$index.'.department_id') is-invalid @enderror">
                <option value="">— Select department —</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}"
                        @selected((string) ($rule['department_id'] ?? '') === (string) $department->id)>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
            @error('department_assignment_rules.'.$index.'.department_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="font-weight-bold d-block">2. Gender</label>
            <p class="small text-muted mb-2">Leave both unchecked for any gender. Example: Female only for Idara ya wanawake.</p>
            <div class="d-flex flex-wrap" style="gap: 1rem;">
                <div class="animated-checkbox">
                    <label>
                        <input type="checkbox"
                               name="department_assignment_rules[{{ $index }}][genders][]"
                               value="male"
                               class="rule-gender"
                               @checked(in_array('male', $selectedGenders, true))>
                        <span class="label-text">Male</span>
                    </label>
                </div>
                <div class="animated-checkbox">
                    <label>
                        <input type="checkbox"
                               name="department_assignment_rules[{{ $index }}][genders][]"
                               value="female"
                               class="rule-gender"
                               @checked(in_array('female', $selectedGenders, true))>
                        <span class="label-text">Female</span>
                    </label>
                </div>
            </div>
            @error('department_assignment_rules.'.$index.'.genders')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="font-weight-bold d-block">3. Age range</label>
            <p class="small text-muted mb-2">Enter both min and max for children/youth rules (e.g. 0–12). Leave blank if age does not matter.</p>
            <div class="row">
                <div class="col-md-6">
                    <label>Min age</label>
                    <input type="number" min="0" max="120"
                           name="department_assignment_rules[{{ $index }}][min_age]"
                           class="form-control rule-min-age @error('department_assignment_rules.'.$index.'.min_age') is-invalid @enderror"
                           value="{{ $rule['min_age'] ?? '' }}"
                           placeholder="e.g. 0">
                    @error('department_assignment_rules.'.$index.'.min_age')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label>Max age</label>
                    <input type="number" min="0" max="120"
                           name="department_assignment_rules[{{ $index }}][max_age]"
                           class="form-control rule-max-age @error('department_assignment_rules.'.$index.'.max_age') is-invalid @enderror"
                           value="{{ $rule['max_age'] ?? '' }}"
                           placeholder="e.g. 12">
                    @error('department_assignment_rules.'.$index.'.max_age')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-group mb-0">
            <label class="font-weight-bold d-block">4. Leadership position</label>
            <p class="small text-muted mb-2">Optional. If selected, the person must hold at least one of these roles. Children are never matched by leadership.</p>
            <div class="row rule-leadership-list">
                @foreach($leadershipPositions as $value => $label)
                    <div class="col-md-6 col-lg-4">
                        <div class="animated-checkbox mb-2">
                            <label>
                                <input type="checkbox"
                                       name="department_assignment_rules[{{ $index }}][leadership_positions][]"
                                       value="{{ $value }}"
                                       class="rule-leadership"
                                       @checked(in_array((string) $value, $selectedPositions, true))>
                                <span class="label-text">{{ $label }}</span>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
            @error('department_assignment_rules.'.$index.'.leadership_positions')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <p class="small text-muted mb-0 mt-3">
            When more than one requirement is set (gender, age, leadership), the person must match <strong>all</strong> of them.
        </p>
    </div>
</div>
