@php
    $childEducationLevels = $childEducationLevels ?? \App\Enums\ChildEducationLevel::cases();
    $isStudentChecked = filter_var(old('is_student', $dependant->is_student ?? false), FILTER_VALIDATE_BOOLEAN);
    $selectedLevel = old('education_level', isset($dependant) ? ($dependant->education_level?->value ?? '') : '');
    $selectedSchoolRegion = old('school_region', $dependant->school_region ?? '');
    $selectedSchoolDistrict = old('school_district', $dependant->school_district ?? '');
@endphp

<hr>
<h5>{{ __('members.fields.school_location') }} / {{ __('members.fields.student_education_level') }}</h5>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <div class="form-check">
                <input type="hidden" name="is_student" value="0">
                <input type="checkbox" name="is_student" id="is_student" value="1" class="form-check-input"
                    @checked($isStudentChecked)>
                <label class="form-check-label" for="is_student">{{ __('members.fields.is_student') }}</label>
            </div>
        </div>
    </div>
</div>
<div id="childEducationFields" class="row" style="display:{{ $isStudentChecked ? 'flex' : 'none' }};">
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('members.fields.student_education_level') }} *</label>
            <select name="education_level" id="education_level" class="form-control @error('education_level') is-invalid @enderror">
                <option value="">{{ __('members.options.select') }}</option>
                @foreach($childEducationLevels as $level)
                    <option value="{{ $level->value }}" @selected($selectedLevel === $level->value)>{{ $level->label() }}</option>
                @endforeach
            </select>
            @error('education_level')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-group">
            <label>{{ __('members.fields.school_name') }} *</label>
            <input type="text" name="school_name" class="form-control @error('school_name') is-invalid @enderror"
                value="{{ old('school_name', $dependant->school_name ?? '') }}">
            @error('school_name')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('members.fields.school_region') }} *</label>
            <select name="school_region" id="school_region" class="form-control @error('school_region') is-invalid @enderror"
                data-selected="{{ $selectedSchoolRegion }}">
                <option value="">{{ __('members.locations.select_region') }}</option>
            </select>
            @error('school_region')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('members.fields.school_district') }} *</label>
            <select name="school_district" id="school_district" class="form-control @error('school_district') is-invalid @enderror"
                data-selected="{{ $selectedSchoolDistrict }}" disabled>
                <option value="">{{ __('members.locations.select_region_first') }}</option>
            </select>
            @error('school_district')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('members.fields.school_ward') }} <small class="text-muted">({{ __('members.fields.school_ward_optional') }})</small></label>
            <input type="text" name="school_ward" class="form-control @error('school_ward') is-invalid @enderror"
                value="{{ old('school_ward', $dependant->school_ward ?? '') }}">
            @error('school_ward')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('members.fields.school_street') }} <small class="text-muted">({{ __('members.fields.school_ward_optional') }})</small></label>
            <input type="text" name="school_street" class="form-control @error('school_street') is-invalid @enderror"
                value="{{ old('school_street', $dependant->school_street ?? '') }}">
            @error('school_street')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
</div>
