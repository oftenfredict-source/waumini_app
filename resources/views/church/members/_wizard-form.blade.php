@php
    $isEdit = $isEdit ?? false;

    $d = function (string $field, mixed $fallback = null) use ($isEdit, $member) {
        $memberValue = $fallback;
        if ($isEdit && isset($member)) {
            $raw = $member->getAttribute($field);
            if ($raw instanceof \BackedEnum) {
                $memberValue = $raw->value;
            } elseif ($raw instanceof \DateTimeInterface) {
                $memberValue = $raw->format('Y-m-d');
            } else {
                $memberValue = $raw ?? $fallback;
            }
        }

        return old($field, $memberValue);
    };

    $normalizePhoneLocal = function (?string $phone): string {
        $phone = $phone ?? '';
        if (str_starts_with($phone, '+255')) {
            return substr($phone, 4);
        }
        if (str_starts_with($phone, '255')) {
            return substr($phone, 3);
        }

        return $phone;
    };

    $resolveTribeSelection = function (?string $tribe, ?string $otherTribe) use ($tribes) {
        if (! $tribe) {
            return ['select' => '', 'other' => $otherTribe ?? ''];
        }
        if (in_array($tribe, $tribes, true)) {
            return ['select' => $tribe, 'other' => $otherTribe ?? ''];
        }

        return ['select' => 'Other', 'other' => $otherTribe ?: $tribe];
    };

    $phoneLocal = $normalizePhoneLocal((string) $d('phone_number', ''));
    $memberTribe = $resolveTribeSelection($d('tribe'), $d('other_tribe'));
    $spousePhoneLocal = $normalizePhoneLocal((string) $d('spouse_phone_number', ''));
    $spouseTribe = $resolveTribeSelection($d('spouse_tribe'), $d('spouse_other_tribe'));
    $baptizedChecked = filter_var($d('is_baptized'), FILTER_VALIDATE_BOOLEAN);
    $hasLinkedSpouse = $isEdit && isset($member) && $member->spouseMember;
@endphp

<div class="tile">    <div class="member-wizard-steps" id="wizardSteps">
        @foreach(['Personal Info', 'Contact & Origin', 'Residence', 'Family Information', 'Summary'] as $i => $label)
            <div class="member-wizard-step {{ $i === 0 ? 'active' : '' }}" data-step="{{ $i + 1 }}">
                <div class="step-circle">{{ $i + 1 }}</div>
                <div class="step-label">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    <form id="memberWizardForm" method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        {{-- STEP 1 --}}
        <div class="wizard-panel active" data-step="1">
            <h3 class="tile-title">Step 1: Personal Information</h3>
            <div class="row">
                @if($isEdit)
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Member ID</label>
                            <input type="text" class="form-control" value="{{ $member->member_number }}" disabled>
                        </div>
                    </div>
                @endif
                @if($branches->isNotEmpty())
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Branch *</label>
                            <select name="branch_id" class="form-control" required>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) $d('branch_id', $defaultBranchId ?? null) === (string) $branch->id)>{{ $branch->displayLabel() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Membership Type *</label>
                        <select name="membership_type" id="membership_type" class="form-control" required>
                            @foreach($membershipTypes as $type)
                                <option value="{{ $type->value }}" @selected($d('membership_type', 'permanent') === $type->value)>{{ ucfirst($type->value) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4" id="memberTypeWrap">
                    <div class="form-group">
                        <label>Member Type *</label>
                        <select name="member_type" id="member_type" class="form-control">
                            <option value="">Select</option>
                            @foreach($memberTypes as $type)
                                <option value="{{ $type->value }}" @selected($d('member_type') === $type->value)>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4" id="temporaryDurationWrap" style="display:none;">
                    <div class="form-group">
                        <label>Stay Duration *</label>
                        <div class="input-group">
                            <input type="number" name="temporary_duration_value" id="temporary_duration_value"
                                class="form-control" min="1" max="99" value="{{ $d('temporary_duration_value', 6) }}">
                            <select name="temporary_duration_unit" id="temporary_duration_unit" class="form-control">
                                @foreach($durationUnits as $unit)
                                    <option value="{{ $unit->value }}" @selected($d('temporary_duration_unit', 'month') === $unit->value)>{{ $unit->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-muted">How long this temporary member will stay (months or years).</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Envelope Number *</label>
                        <input type="text" name="envelope_number" id="envelope_number" class="form-control"
                            maxlength="3" pattern="\d{3}" value="{{ $d('envelope_number') }}" required>
                        <div id="envelope_status" class="envelope-status"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" class="form-control" value="{{ $d('full_name') }}" required>
                    </div>
                </div>
                <div class="col-md-3" id="genderFieldWrap">
                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender" id="gender" class="form-control" required>
                            <option value="">Select</option>
                            <option value="male" @selected($d('gender') === 'male')>Male</option>
                            <option value="female" @selected($d('gender') === 'female')>Female</option>
                        </select>
                        <small class="text-muted">Required for independent members</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Date of Birth *</label>
                        <input type="date" name="date_of_birth" class="form-control" value="{{ $d('date_of_birth') }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Education Level</label>
                        <select name="education_level" class="form-control">
                            <option value="">Select</option>
                            @foreach($educationLevels as $level)
                                <option value="{{ $level->value }}" @selected($d('education_level') === $level->value)>{{ $level->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Profession</label>
                        <input type="text" name="profession" class="form-control" value="{{ $d('profession') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>NIDA Number</label>
                        <input type="text" name="nida_number" class="form-control" value="{{ $d('nida_number') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Passport Picture</label>
                        <input type="file" name="profile_picture" id="profile_picture" class="form-control-file" accept="image/*">
                        <img id="profile_preview" class="profile-preview mt-2"
                            src="{{ $isEdit && $member->profilePictureUrl() ? $member->profilePictureUrl() : '' }}"
                            style="{{ $isEdit && $member->profilePictureUrl() ? '' : 'display:none;' }}" alt="Preview">
                    </div>
                </div>
            </div>

            <h4 class="mt-3 mb-3">Baptism Information</h4>
            <div class="row">
                <div class="col-md-12">
                    <div class="animated-checkbox mb-3">
                        <label>
                            <input type="checkbox" name="is_baptized" id="is_baptized" value="1" @checked($baptizedChecked)>
                            <span class="label-text">This member has been baptized</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-12" id="memberBaptismFields" style="display:{{ $baptizedChecked ? 'block' : 'none' }};">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Baptism Date</label>
                                <input type="date" name="baptism_date" class="form-control" value="{{ $d('baptism_date') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Place of Baptism</label>
                                <input type="text" name="baptism_place" class="form-control" value="{{ $d('baptism_place') }}"
                                       placeholder="Church or location">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Baptized By (Minister)</label>
                                <input type="text" name="baptized_by" class="form-control" value="{{ $d('baptized_by') }}"
                                       placeholder="Pastor / minister name">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 2 --}}
        <div class="wizard-panel" data-step="2">
            <h3 class="tile-title">Step 2: Contact & Origin</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <div class="input-group phone-input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">+255</span>
                            </div>
                            <input type="tel" name="phone_number" id="phone_number" class="form-control"
                                value="{{ $phoneLocal }}" placeholder="712345678" inputmode="numeric"
                                pattern="[0-9]{9}" maxlength="9" required>
                        </div>
                        <small class="text-muted">Enter number without +255 (e.g. 712345678)</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $d('email') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Origin Region</label>
                        <select name="region" id="region" class="form-control" data-selected="{{ $d('region') }}">
                            <option value="">Loading regions...</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>District</label>
                        <select name="district" id="district" class="form-control" data-selected="{{ $d('district') }}" disabled>
                            <option value="">Select region first</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Ward</label>
                        <input type="text" name="ward" class="form-control" value="{{ $d('ward') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Street</label>
                        <input type="text" name="street" class="form-control" value="{{ $d('street') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>P.O. Box</label>
                        <input type="text" name="po_box" class="form-control" value="{{ $d('po_box') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tribe</label>
                        <select name="tribe" id="tribe" class="form-control">
                            <option value="">Select tribe</option>
                            @foreach($tribes as $tribe)
                                <option value="{{ $tribe }}" @selected($memberTribe['select'] === $tribe)>{{ $tribe }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4" id="otherTribeWrap" style="display:{{ $memberTribe['select'] === 'Other' ? 'block' : 'none' }};">
                    <div class="form-group">
                        <label>Specify Tribe *</label>
                        <input type="text" name="other_tribe" id="other_tribe" class="form-control"
                            value="{{ $memberTribe['other'] }}" placeholder="Enter tribe name">
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 3 --}}
        <div class="wizard-panel" data-step="3">
            <h3 class="tile-title">Step 3: Residence</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Region</label>
                        <select name="residence_region" id="residence_region" class="form-control" data-selected="{{ $d('residence_region') }}">
                            <option value="">Loading regions...</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>District</label>
                        <select name="residence_district" id="residence_district" class="form-control" data-selected="{{ $d('residence_district') }}" disabled>
                            <option value="">Select region first</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Ward</label>
                        <input type="text" name="residence_ward" class="form-control" value="{{ $d('residence_ward') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Street</label>
                        <input type="text" name="residence_street" class="form-control" value="{{ $d('residence_street') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Road Name</label>
                        <input type="text" name="residence_road" class="form-control" value="{{ $d('residence_road') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>House Number</label>
                        <input type="text" name="residence_house_number" class="form-control" value="{{ $d('residence_house_number') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 4 --}}
        <div class="wizard-panel" data-step="4">
            <h3 class="tile-title">Step 4: Family Information</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Marital Status *</label>
                        <select name="marital_status" id="marital_status" class="form-control" required>
                            @foreach($maritalStatuses as $status)
                                <option value="{{ $status->value }}" @selected($d('marital_status') === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div id="weddingSection" style="display:none;">
                <h4 class="mt-3 mb-3">Wedding Information</h4>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Type of Wedding *</label>
                            <select name="wedding_type" id="wedding_type" class="form-control">
                                <option value="">Select wedding type</option>
                                @foreach($weddingTypes as $wType)
                                    <option value="{{ $wType->value }}" @selected($d('wedding_type') === $wType->value)>{{ $wType->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Date of Wedding <small class="text-muted">(optional)</small></label>
                            <input type="date" name="wedding_date" id="wedding_date" class="form-control"
                                value="{{ $d('wedding_date') }}" max="{{ now()->toDateString() }}">
                        </div>
                    </div>
                </div>
            </div>

            @if($hasLinkedSpouse)
                <div id="editSpouseInfo" style="display:none;">
                    <h4 class="mt-3 mb-3">Spouse</h4>
                    <p class="text-muted">
                        <i class="fa fa-info-circle"></i>
                        Linked member:
                        <a href="{{ route('church.members.show', $member->spouseMember) }}">{{ $member->spouseMember->full_name }}</a>
                        (<code>{{ $member->spouseMember->member_number }}</code>).
                        Update spouse details from their profile.
                    </p>
                </div>
            @else
            <div id="spouseSection" style="display:none;">
                <h4 class="mt-3 mb-3">Spouse Information</h4>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Is your spouse a church member? *</label>
                            <select name="spouse_church_member" id="spouse_church_member" class="form-control">
                                <option value="no" @selected($d('spouse_church_member', 'no') === 'no')>No</option>
                                <option value="yes" @selected($d('spouse_church_member') === 'yes')>Yes</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8" id="spouseInputMethodWrap" style="display:none;">
                        <div class="form-group">
                            <label>How do you want to add spouse details? *</label>
                            <select name="spouse_input_method" id="spouse_input_method" class="form-control">
                                <option value="select" @selected($d('spouse_input_method', 'select') === 'select')>Select existing church member</option>
                                <option value="manual" @selected($d('spouse_input_method') === 'manual')>Fill spouse information manually</option>
                            </select>
                            <small class="text-muted">Choose one option — select from the list or enter details yourself.</small>
                        </div>
                    </div>
                </div>

                <div id="spouseMemberSelect" style="display:none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Select Spouse (Member) *</label>
                                <select name="spouse_member_id" id="spouse_member_id" class="form-control">
                                    <option value="">Select member</option>
                                    @foreach($churchMembers ?? [] as $cm)
                                        <option value="{{ $cm->id }}"
                                            data-envelope="{{ $cm->envelope_number }}"
                                            data-name="{{ $cm->full_name }}"
                                            data-gender="{{ $cm->gender }}"
                                            data-dob="{{ $cm->date_of_birth?->format('Y-m-d') }}"
                                            data-phone="{{ $cm->phone_number }}"
                                            data-email="{{ $cm->email }}"
                                            @selected((string) $d('spouse_member_id') === (string) $cm->id)>
                                            {{ $cm->full_name }} ({{ $cm->member_number }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Spouse Envelope Number *</label>
                                <input type="text" name="spouse_envelope_number" id="spouse_envelope_number_select"
                                    class="form-control spouse-envelope-field" maxlength="3" pattern="\d{3}"
                                    value="{{ $d('spouse_envelope_number') }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="spouseManualFields">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Spouse Full Name <span class="spouse-required-mark">*</span></label>
                                <input type="text" name="spouse_full_name" class="form-control" value="{{ $d('spouse_full_name') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Spouse Gender <span class="spouse-required-mark">*</span></label>
                                <select name="spouse_gender" id="spouse_gender" class="form-control">
                                    <option value="">Select</option>
                                    <option value="male" @selected($d('spouse_gender') === 'male')>Male</option>
                                    <option value="female" @selected($d('spouse_gender') === 'female')>Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Spouse Date of Birth <span class="spouse-required-mark">*</span></label>
                                <input type="date" name="spouse_date_of_birth" class="form-control" value="{{ $d('spouse_date_of_birth') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Spouse Education Level</label>
                                <select name="spouse_education_level" class="form-control">
                                    <option value="">Select</option>
                                    @foreach($educationLevels as $level)
                                        <option value="{{ $level->value }}" @selected($d('spouse_education_level') === $level->value)>{{ $level->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Spouse Profession</label>
                                <input type="text" name="spouse_profession" class="form-control" value="{{ $d('spouse_profession') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Spouse NIDA Number</label>
                                <input type="text" name="spouse_nida_number" class="form-control" value="{{ $d('spouse_nida_number') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Spouse Phone</label>
                                <div class="input-group phone-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">+255</span>
                                    </div>
                                    <input type="tel" name="spouse_phone_number" id="spouse_phone_number" class="form-control"
                                        value="{{ $spousePhoneLocal }}" placeholder="712345678" inputmode="numeric"
                                        maxlength="9" pattern="[0-9]{9}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Spouse Email</label>
                                <input type="email" name="spouse_email" class="form-control" value="{{ $d('spouse_email') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Spouse Tribe</label>
                                <select name="spouse_tribe" id="spouse_tribe" class="form-control">
                                    <option value="">Select tribe</option>
                                    @foreach($tribes as $tribe)
                                        <option value="{{ $tribe }}" @selected($spouseTribe['select'] === $tribe)>{{ $tribe }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4" id="spouseOtherTribeWrap" style="display:{{ $spouseTribe['select'] === 'Other' ? 'block' : 'none' }};">
                            <div class="form-group">
                                <label>Specify Spouse Tribe *</label>
                                <input type="text" name="spouse_other_tribe" id="spouse_other_tribe" class="form-control"
                                    value="{{ $spouseTribe['other'] }}" placeholder="Enter tribe name">
                            </div>
                        </div>
                        <div class="col-md-4" id="spouseEnvelopeManualWrap" style="display:none;">
                            <div class="form-group">
                                <label>Spouse Envelope Number *</label>
                                <input type="text" id="spouse_envelope_number_manual"
                                    class="form-control spouse-envelope-field" maxlength="3" pattern="\d{3}"
                                    value="{{ $d('spouse_envelope_number') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($isEdit)
                <div class="form-group mt-4">
                    <label>Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="form-control">{{ $d('notes') }}</textarea>
                </div>
            @endif

            @if(! $isEdit)
            <h4 class="mt-4 mb-3">Dependants / Family Members Living Together</h4>
            <p class="text-muted">Add children or other relatives who live with this member.</p>
            <div id="dependantsContainer"></div>
            <button type="button" class="btn btn-outline-primary btn-sm" id="addDependantBtn">
                <i class="fa fa-plus"></i> Add Family Member
            </button>
            @endif
        </div>

        {{-- STEP 5 --}}
        <div class="wizard-panel" data-step="5">
            <h3 class="tile-title">Step 5: Summary</h3>
            <p class="text-muted mb-3">{{ $isEdit ? 'Review all information before saving changes.' : 'Review all information before saving the member.' }}</p>
            <div id="summaryContent"></div>
        </div>

        <div class="tile-footer d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-secondary" id="prevStepBtn" style="display:none;">
                <i class="fa fa-arrow-left"></i> Previous
            </button>
            <div class="ml-auto">
                <a href="{{ $cancelUrl }}" class="btn btn-light mr-2">Cancel</a>
                <button type="button" class="btn btn-primary" id="nextStepBtn">
                    Next <i class="fa fa-arrow-right"></i>
                </button>
                <button type="submit" class="btn btn-primary" id="submitBtn" style="display:none;">
                    <i class="fa fa-save"></i> {{ $submitLabel }}
                </button>
            </div>
        </div>
    </form>
</div>

@if(! $isEdit)
<template id="dependantTemplate">
    <div class="dependant-row">
        <div class="d-flex justify-content-between mb-2">
            <strong>Family Member</strong>
            <button type="button" class="btn btn-sm btn-danger remove-dependant"><i class="fa fa-times"></i></button>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" class="form-control dependant-name" data-name="full_name" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Gender *</label>
                    <select class="form-control dependant-gender" data-name="gender" required>
                        <option value="">Select</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" class="form-control dependant-dob" data-name="date_of_birth">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Relationship *</label>
                    <select class="form-control dependant-relationship" data-name="relationship" required>
                        @foreach($dependantRelationships as $rel)
                            <option value="{{ $rel->value }}">{{ $rel->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Notes (optional)</label>
                    <input type="text" class="form-control dependant-note" data-name="relationship_note" placeholder="e.g. nephew, niece">
                </div>
            </div>
            <div class="col-md-12">
                <div class="animated-checkbox mb-2">
                    <label>
                        <input type="checkbox" class="dependant-baptized" data-name="is_baptized" value="1">
                        <span class="label-text">Baptized</span>
                    </label>
                </div>
            </div>
            <div class="col-md-12 dependant-baptism-fields" style="display:none;">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Baptism Date</label>
                            <input type="date" class="form-control dependant-baptism-date" data-name="baptism_date">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Place of Baptism</label>
                            <input type="text" class="form-control dependant-baptism-place" data-name="baptism_place">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Baptized By</label>
                            <input type="text" class="form-control dependant-baptized-by" data-name="baptized_by">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
@endif
