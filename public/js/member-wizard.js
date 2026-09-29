(function () {
    var currentStep = 1;
    var totalSteps = 5;
    var form = document.getElementById('memberWizardForm');
    if (!form) return;

    var panels = document.querySelectorAll('.wizard-panel');
    var stepIndicators = document.querySelectorAll('.member-wizard-step');
    var prevBtn = document.getElementById('prevStepBtn');
    var nextBtn = document.getElementById('nextStepBtn');
    var submitBtn = document.getElementById('submitBtn');
    var dependantIndex = 0;

    function showStep(step) {
        currentStep = step;
        panels.forEach(function (panel) {
            panel.classList.toggle('active', parseInt(panel.dataset.step, 10) === step);
        });
        stepIndicators.forEach(function (indicator) {
            var n = parseInt(indicator.dataset.step, 10);
            indicator.classList.toggle('active', n === step);
            indicator.classList.toggle('completed', n < step);
        });
        prevBtn.style.display = step > 1 ? 'inline-block' : 'none';
        nextBtn.style.display = step < totalSteps ? 'inline-block' : 'none';
        submitBtn.style.display = step === totalSteps ? 'inline-block' : 'none';
        if (step === 4) {
            toggleIndependentFamilySection();
        }
        if (step === totalSteps) buildSummary();
        updateRegisterProgress(step);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function updateRegisterProgress(step) {
        var fill = document.getElementById('registerProgressFill');
        var label = document.getElementById('registerProgressLabel');
        var stepName = document.getElementById('registerProgressStepName');
        if (!fill) return;

        fill.style.width = ((step / totalSteps) * 100) + '%';
        if (label) {
            if (window.registerStepProgress && window.registerStepProgress.template) {
                label.textContent = window.registerStepProgress.template
                    .replace(':current', String(step))
                    .replace(':total', String(window.registerStepProgress.total || totalSteps));
            } else {
                label.textContent = step;
            }
        }
        if (stepName && window.registerStepNames) {
            stepName.textContent = window.registerStepNames[step - 1] || '';
        }
    }

    function getField(name) {
        var el = form.querySelector('[name="' + name + '"]');
        return el ? el.value : '';
    }

    function getSelectText(name) {
        var el = form.querySelector('[name="' + name + '"]');
        if (!el || !el.options || el.selectedIndex < 0) return '';
        return el.options[el.selectedIndex].text;
    }

    function getTribeDisplay(tribeField, otherField) {
        var tribe = getSelectText(tribeField) || getField(tribeField);
        var other = getField(otherField);
        if (tribe === 'Other' && other) {
            return other;
        }

        return tribe || other || '';
    }

    function toggleOtherTribe(selectId, wrapId, otherInputId) {
        var select = document.getElementById(selectId);
        var wrap = document.getElementById(wrapId);
        var otherInput = otherInputId ? document.getElementById(otherInputId) : null;
        if (!select || !wrap) return;

        var isOther = select.value === 'Other';
        wrap.style.display = isOther ? 'block' : 'none';

        if (otherInput) {
            otherInput.required = isOther;
            if (!isOther) {
                otherInput.value = '';
            }
        }
    }

    function formatPhoneDisplay() {
        var local = getField('phone_number').replace(/\s+/g, '');
        return local ? '+255' + local : '';
    }

    function formatPhoneForSubmit() {
        formatLocalPhoneField('phone_number');
        formatLocalPhoneField('spouse_phone_number');
    }

    function formatLocalPhoneField(fieldId) {
        var phoneInput = document.getElementById(fieldId);
        if (!phoneInput || phoneInput.disabled) return;
        var value = phoneInput.value.replace(/\s+/g, '');
        if (!value) return;
        if (value.indexOf('+255') === 0) return;
        if (value.indexOf('255') === 0 && value.length > 9) {
            value = value.substring(3);
        }
        if (value.charAt(0) === '0') {
            value = value.substring(1);
        }
        phoneInput.value = '+255' + value;
    }

    function formatSpousePhoneDisplay() {
        var local = getField('spouse_phone_number').replace(/\s+/g, '');
        return local ? '+255' + local : '';
    }

    function wizardI18n(path, fallback) {
        var value = window.memberWizardI18n;
        if (!value) {
            return fallback || path;
        }

        path.split('.').forEach(function (part) {
            value = value && Object.prototype.hasOwnProperty.call(value, part) ? value[part] : null;
        });

        return value || fallback || path;
    }

    function initTanzaniaLocations() {
        var regionEl = document.getElementById('region');
        var districtEl = document.getElementById('district');
        var residenceRegionEl = document.getElementById('residence_region');
        var residenceDistrictEl = document.getElementById('residence_district');
        var locationsUrl = window.memberWizardConfig && window.memberWizardConfig.locationsUrl;

        window.tanzaniaRegions = window.tanzaniaRegions || [];

        function populateRegions(selectEl, selected) {
            if (!selectEl) return;
            selectEl.innerHTML = '<option value="">' + wizardI18n('locations.select_region', 'Select region') + '</option>';
            (window.tanzaniaRegions || []).forEach(function (region) {
                var option = document.createElement('option');
                option.value = region.name;
                option.textContent = region.name;
                if (region.name === selected) option.selected = true;
                selectEl.appendChild(option);
            });
        }

        function populateDistricts(regionName, districtSelect, selectedDistrict) {
            if (!districtSelect) return;
            var region = (window.tanzaniaRegions || []).find(function (item) { return item.name === regionName; });
            var districts = region ? region.districts : [];

            districtSelect.innerHTML = '<option value="">' + (
                regionName
                    ? wizardI18n('locations.select_district', 'Select district')
                    : wizardI18n('locations.select_region_first', 'Select region first')
            ) + '</option>';
            districts.forEach(function (district) {
                var option = document.createElement('option');
                option.value = district.name;
                option.textContent = district.name;
                if (district.name === selectedDistrict) option.selected = true;
                districtSelect.appendChild(option);
            });

            districtSelect.disabled = !regionName;
        }

        window.populateTanzaniaRegions = populateRegions;
        window.populateTanzaniaDistricts = populateDistricts;

        window.bindDependantSchoolLocation = function (row) {
            var schoolRegion = row.querySelector('.dependant-school-region');
            var schoolDistrict = row.querySelector('.dependant-school-district');
            if (!schoolRegion || !schoolDistrict) return;

            var selectedRegion = schoolRegion.getAttribute('data-selected') || schoolRegion.value || '';
            var selectedDistrict = schoolDistrict.getAttribute('data-selected') || schoolDistrict.value || '';

            populateRegions(schoolRegion, selectedRegion);
            populateDistricts(selectedRegion, schoolDistrict, selectedDistrict);

            if (!schoolRegion.dataset.boundLocation) {
                schoolRegion.addEventListener('change', function () {
                    populateDistricts(this.value, schoolDistrict, '');
                });
                schoolRegion.dataset.boundLocation = '1';
            }
        };

        if (!locationsUrl) return;

        fetch(locationsUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                window.tanzaniaRegions = data.regions || [];

                if (regionEl && districtEl) {
                    var savedRegion = regionEl.getAttribute('data-selected') || '';
                    var savedDistrict = districtEl.getAttribute('data-selected') || '';
                    populateRegions(regionEl, savedRegion);
                    if (savedRegion) populateDistricts(savedRegion, districtEl, savedDistrict);
                    regionEl.addEventListener('change', function () {
                        populateDistricts(this.value, districtEl, '');
                    });
                }

                if (residenceRegionEl && residenceDistrictEl) {
                    var savedResidenceRegion = residenceRegionEl.getAttribute('data-selected') || '';
                    var savedResidenceDistrict = residenceDistrictEl.getAttribute('data-selected') || '';
                    populateRegions(residenceRegionEl, savedResidenceRegion);
                    if (savedResidenceRegion) {
                        populateDistricts(savedResidenceRegion, residenceDistrictEl, savedResidenceDistrict);
                    }
                    residenceRegionEl.addEventListener('change', function () {
                        populateDistricts(this.value, residenceDistrictEl, '');
                    });
                }

                document.querySelectorAll('#dependantsContainer .dependant-row').forEach(function (row) {
                    if (typeof window.bindDependantSchoolLocation === 'function') {
                        window.bindDependantSchoolLocation(row);
                    }
                });
            })
            .catch(function () {
                if (regionEl) {
                    regionEl.innerHTML = '<option value="">' + wizardI18n('locations.unable_load_regions', 'Unable to load regions') + '</option>';
                }
                if (residenceRegionEl) {
                    residenceRegionEl.innerHTML = '<option value="">' + wizardI18n('locations.unable_load_regions', 'Unable to load regions') + '</option>';
                }
            });
    }

    function isInHiddenContainer(input) {
        if (input.disabled || input.type === 'hidden') {
            return true;
        }

        var el = input.parentElement;
        while (el && el !== form) {
            if (el.classList && el.classList.contains('wizard-panel')) {
                return false;
            }

            var style = window.getComputedStyle(el);
            if (style.display === 'none' || style.visibility === 'hidden') {
                return true;
            }

            el = el.parentElement;
        }

        return false;
    }

    function validateStep(step, forFinalSubmit) {
        var panel = form.querySelector('.wizard-panel[data-step="' + step + '"]');
        if (!panel) {
            return true;
        }

        var inputs = panel.querySelectorAll('input, select, textarea');
        for (var i = 0; i < inputs.length; i++) {
            var input = inputs[i];
            if (input.disabled || input.type === 'hidden') {
                continue;
            }

            if (forFinalSubmit) {
                if (isInHiddenContainer(input)) {
                    continue;
                }
            } else if (input.offsetParent === null) {
                continue;
            }

            if (!input.checkValidity()) {
                if (forFinalSubmit) {
                    showStep(step);
                }
                input.reportValidity();
                return false;
            }
        }

        return true;
    }

    function syncDependantNames() {
        var rows = document.querySelectorAll('#dependantsContainer .dependant-row');
        rows.forEach(function (row, index) {
            row.querySelectorAll('[data-name]').forEach(function (field) {
                field.name = 'dependants[' + index + '][' + field.getAttribute('data-name') + ']';
            });
        });
    }

    function buildSummary() {
        syncDependantNames();
        var s = function (key, fallback) {
            return wizardI18n('summary.' + key, fallback);
        };
        var yesNo = function (checked) {
            return checked ? wizardI18n('yes', 'Yes') : wizardI18n('no', 'No');
        };
        var html = '<div class="row">';
        html += summaryBlock(s('personal_information', 'Personal Information'), [
            [s('membership_type', 'Membership Type'), getSelectText('membership_type')],
            [s('stay_duration', 'Stay Duration'), getField('membership_type') === 'temporary'
                ? (getField('temporary_duration_value') + ' ' + getSelectText('temporary_duration_unit'))
                : ''],
            [s('member_type', 'Member Type'), getSelectText('member_type')],
            [s('envelope_number', 'Envelope Number'), getField('envelope_number')],
            [s('full_name', 'Full Name'), getField('full_name')],
            [s('gender', 'Gender'), getSelectText('gender')],
            [s('date_of_birth', 'Date of Birth'), getField('date_of_birth')],
            [s('education_level', 'Education Level'), getSelectText('education_level')],
            [s('profession', 'Profession'), getField('profession')],
            [s('nida_number', 'NIDA Number'), getField('nida_number')],
            [s('baptized', 'Baptized'), yesNo(document.getElementById('is_baptized')?.checked)],
            [s('baptism_date', 'Baptism Date'), document.getElementById('is_baptized')?.checked ? getField('baptism_date') : ''],
            [s('baptism_place', 'Baptism Place'), document.getElementById('is_baptized')?.checked ? getField('baptism_place') : ''],
            [s('baptized_by', 'Baptized By'), document.getElementById('is_baptized')?.checked ? getField('baptized_by') : ''],
            [s('kipaimara', 'Kipaimara'), document.getElementById('is_kipaimara') ? yesNo(document.getElementById('is_kipaimara')?.checked) : ''],
            [s('kipaimara_date', 'Kipaimara Date'), document.getElementById('is_kipaimara')?.checked ? getField('kipaimara_date') : ''],
            [s('kipaimara_place', 'Kipaimara Place'), document.getElementById('is_kipaimara')?.checked ? getField('kipaimara_place') : ''],
            [s('kipaimara_by', 'Confirmed By (Bishop)'), document.getElementById('is_kipaimara')?.checked ? getField('kipaimara_by') : ''],
        ]);
        html += summaryBlock(s('contact_origin', 'Contact & Origin'), [
            [s('phone', 'Phone'), formatPhoneDisplay()],
            [s('email', 'Email'), getField('email')],
            [s('origin_region', 'Origin Region'), getSelectText('region')],
            [s('district', 'District'), getSelectText('district')],
            [s('ward', 'Ward'), getField('ward')],
            [s('street', 'Street'), getField('street')],
            [s('po_box', 'P.O. Box'), getField('po_box')],
            [s('tribe', 'Tribe'), getTribeDisplay('tribe', 'other_tribe')],
        ]);
        html += summaryBlock(s('residence', 'Residence'), [
            [s('region', 'Region'), getSelectText('residence_region')],
            [s('district', 'District'), getSelectText('residence_district')],
            [s('ward', 'Ward'), getField('residence_ward')],
            [s('street', 'Street'), getField('residence_street')],
            [s('road', 'Road'), getField('residence_road')],
            [s('house_number', 'House Number'), getField('residence_house_number')],
        ]);
        html += summaryBlock(s('family_information', 'Family Information'), (function () {
            var rows = [
                [s('marital_status', 'Marital Status'), getSelectText('marital_status')],
            ];
            if (getField('marital_status') === 'married') {
                rows.push([s('wedding_type', 'Wedding Type'), getSelectText('wedding_type')]);
                rows.push([s('wedding_date', 'Wedding Date'), getField('wedding_date')]);
                rows.push([s('spouse_church_member', 'Spouse Church Member'), getSelectText('spouse_church_member')]);
                if (getField('spouse_church_member') === 'yes') {
                    if (!isSelfRegistration()) {
                        rows.push([s('spouse_entry_method', 'Spouse Entry Method'), getSelectText('spouse_input_method')]);
                    }
                    if (!isSelfRegistration() && getField('spouse_input_method') === 'select') {
                        rows.push([s('spouse_selected', 'Spouse (Selected)'), getSelectText('spouse_member_id')]);
                        rows.push([s('spouse_envelope', 'Spouse Envelope'), document.getElementById('spouse_envelope_number_select')?.value]);
                    } else {
                        rows.push([s('spouse_name', 'Spouse Name'), getField('spouse_full_name')]);
                        if (!isSelfRegistration()) {
                            rows.push([s('spouse_envelope', 'Spouse Envelope'), document.getElementById('spouse_envelope_number_manual')?.value]);
                        }
                        rows.push([s('spouse_gender', 'Spouse Gender'), getSelectText('spouse_gender')]);
                        rows.push([s('spouse_dob', 'Spouse DOB'), getField('spouse_date_of_birth')]);
                        rows.push([s('spouse_phone', 'Spouse Phone'), formatSpousePhoneDisplay()]);
                        rows.push([s('spouse_tribe', 'Spouse Tribe'), getTribeDisplay('spouse_tribe', 'spouse_other_tribe')]);
                    }
                } else {
                    rows.push([s('spouse_name', 'Spouse Name'), getField('spouse_full_name')]);
                    rows.push([s('spouse_gender', 'Spouse Gender'), getSelectText('spouse_gender')]);
                    rows.push([s('spouse_dob', 'Spouse DOB'), getField('spouse_date_of_birth')]);
                    rows.push([s('spouse_phone', 'Spouse Phone'), formatSpousePhoneDisplay()]);
                    rows.push([s('spouse_tribe', 'Spouse Tribe'), getTribeDisplay('spouse_tribe', 'spouse_other_tribe')]);
                }
            }
            if (getField('member_type') === 'independent') {
                rows = [
                    [s('marital_status', 'Marital Status'), s('independent_single_note', 'Single (independent)')],
                    [s('family_parent_type', 'Family / Guardian'), getSelectText('family_parent_type')],
                    [s('guardian_relationship', 'Relationship'), getSelectText('guardian_relationship') || getField('guardian_relationship')],
                ];
                if (getField('family_parent_type') === 'member') {
                    rows.push([s('family_member', 'Lives with member'), getSelectText('family_member_id')]);
                    var familySelect = document.getElementById('family_member_id');
                    var familyOption = familySelect ? familySelect.options[familySelect.selectedIndex] : null;
                    var spouseName = familyOption ? (familyOption.getAttribute('data-spouse-name') || '') : '';
                    if (spouseName) {
                        rows.push([s('linked_spouse_parent', 'Also linked'), spouseName]);
                    }
                }
                if (getField('family_parent_type') === 'guardian') {
                    rows.push([s('guardian_full_name', 'Guardian'), getField('guardian_full_name')]);
                    rows.push([s('guardian_phone', 'Guardian Phone'), getField('guardian_phone')]);
                }
            }
            if (window.memberWizardConfig && window.memberWizardConfig.isEdit) {
                var notes = getField('notes');
                if (notes) {
                    rows.push([s('notes', 'Notes'), notes]);
                }
            }
            return rows;
        })());
        html += '</div>';

        var dependants = document.querySelectorAll('#dependantsContainer .dependant-row');
        if (dependants.length) {
            html += '<h5 class="mt-3">' + s('dependants', 'Dependants') + '</h5><ul class="list-group">';
            dependants.forEach(function (row) {
                var name = row.querySelector('.dependant-name').value;
                var rel = row.querySelector('.dependant-relationship');
                var relText = rel.options[rel.selectedIndex].text;
                html += '<li class="list-group-item">' + name + ' — ' + relText + '</li>';
            });
            html += '</ul>';
        }

        document.getElementById('summaryContent').innerHTML = html;
    }

    function summaryBlock(title, rows) {
        var html = '<div class="col-md-6 mb-3"><div class="tile"><h4>' + title + '</h4><table class="table table-sm summary-table">';
        rows.forEach(function (row) {
            if (row[1]) {
                html += '<tr><th>' + row[0] + '</th><td>' + row[1] + '</td></tr>';
            }
        });
        html += '</table></div></div>';
        return html;
    }

    function toggleMemberType() {
        var permanent = getField('membership_type') === 'permanent';
        var temporary = getField('membership_type') === 'temporary';
        var memberTypeWrap = document.getElementById('memberTypeWrap');
        var temporaryWrap = document.getElementById('temporaryDurationWrap');
        var memberType = document.getElementById('member_type');
        var durationValue = document.getElementById('temporary_duration_value');
        var durationUnit = document.getElementById('temporary_duration_unit');

        if (memberTypeWrap) memberTypeWrap.style.display = permanent ? 'block' : 'none';
        if (temporaryWrap) temporaryWrap.style.display = temporary ? 'block' : 'none';

        if (memberType) {
            memberType.required = permanent;
            if (!permanent) memberType.value = '';
        }

        if (durationValue) durationValue.required = temporary;
        if (durationUnit) durationUnit.required = temporary;

        toggleGenderField();
        toggleIndependentFamilySection();
    }

    function toggleIndependentFamilySection() {
        var section = document.getElementById('independentFamilySection');
        var maritalSection = document.getElementById('maritalStatusSection');
        var independentNote = document.getElementById('independentMaritalNote');
        var dependantsSection = document.getElementById('dependantsSection');
        var typeHint = document.getElementById('independentMemberTypeHint');
        var maritalStatus = document.getElementById('marital_status');
        var membershipType = (getField('membership_type') || '').toLowerCase().trim();
        var memberType = (getField('member_type') || '').toLowerCase().trim();
        var show = membershipType === 'permanent' && memberType === 'independent';

        if (form) {
            form.classList.toggle('wizard-is-independent', show);
        }
        if (typeHint) {
            typeHint.style.display = membershipType === 'permanent' ? 'block' : 'none';
        }

        if (section) {
            if (show) {
                section.removeAttribute('hidden');
                section.classList.add('is-visible');
            } else {
                section.setAttribute('hidden', 'hidden');
                section.classList.remove('is-visible');
            }
            section.style.display = '';
        }
        if (maritalSection) maritalSection.style.display = '';
        if (independentNote) independentNote.style.display = '';
        if (dependantsSection) dependantsSection.style.display = '';

        if (show && maritalStatus) {
            maritalStatus.value = 'single';
            maritalStatus.removeAttribute('required');
            toggleSpouseSection();
        } else if (maritalStatus) {
            maritalStatus.required = true;
        }

        toggleIndependentFamilyFields();
    }

    function toggleIndependentFamilyFields() {
        var section = document.getElementById('independentFamilySection');
        var memberSection = document.getElementById('independentFamilyMemberSection');
        var guardianSection = document.getElementById('independentGuardianSection');
        var familyType = document.getElementById('family_parent_type');
        var familyMember = document.getElementById('family_member_id');
        var guardianName = document.getElementById('guardian_full_name');
        var relationship = document.getElementById('guardian_relationship');
        var secondaryHint = document.getElementById('secondaryFamilyMemberHint');
        var membershipType = (getField('membership_type') || '').toLowerCase().trim();
        var memberType = (getField('member_type') || '').toLowerCase().trim();
        var independentActive = membershipType === 'permanent' && memberType === 'independent';

        if (!independentActive) {
            if (familyType) familyType.removeAttribute('required');
            if (familyMember) familyMember.removeAttribute('required');
            if (guardianName) guardianName.removeAttribute('required');
            if (relationship) relationship.removeAttribute('required');
            if (secondaryHint) secondaryHint.style.display = 'none';
            if (form) form.classList.remove('wizard-family-guardian', 'wizard-family-member');
            return;
        }

        var type = (getField('family_parent_type') || 'member').toLowerCase().trim();
        if (form) {
            form.classList.toggle('wizard-family-guardian', type === 'guardian');
            form.classList.toggle('wizard-family-member', type !== 'guardian');
        }
        if (familyType) familyType.required = true;
        if (relationship) relationship.required = true;
        if (memberSection) {
            memberSection.style.display = type === 'member' ? '' : 'none';
            memberSection.hidden = type !== 'member';
        }
        if (guardianSection) {
            guardianSection.style.display = type === 'guardian' ? '' : 'none';
            guardianSection.hidden = type !== 'guardian';
        }

        if (familyMember) {
            if (type === 'member') {
                familyMember.required = true;
                updateSecondaryFamilyHint();
            } else {
                familyMember.removeAttribute('required');
                familyMember.value = '';
                if (secondaryHint) secondaryHint.style.display = 'none';
            }
        }

        if (guardianName) {
            if (type === 'guardian') {
                guardianName.required = true;
            } else {
                guardianName.removeAttribute('required');
                guardianName.value = '';
                var phone = document.getElementById('guardian_phone');
                if (phone) phone.value = '';
            }
        }
    }

    function updateSecondaryFamilyHint() {
        var select = document.getElementById('family_member_id');
        var hint = document.getElementById('secondaryFamilyMemberHint');
        if (!select || !hint) return;

        var option = select.options[select.selectedIndex];
        var spouseName = option ? (option.getAttribute('data-spouse-name') || '') : '';
        if (spouseName) {
            hint.style.display = 'block';
            hint.textContent = (window.memberWizardConfig && window.memberWizardConfig.labels && window.memberWizardConfig.labels.secondary_family_hint)
                ? window.memberWizardConfig.labels.secondary_family_hint.replace(':name', spouseName)
                : ('Also linking spouse/parent: ' + spouseName);
        } else {
            hint.style.display = 'none';
            hint.textContent = '';
        }
    }

    function toggleGenderField() {
        var memberType = getField('member_type');
        var wrap = document.getElementById('genderFieldWrap');
        var genderEl = document.getElementById('gender');
        if (!wrap || !genderEl) return;

        var autoGender = null;
        if (memberType === 'father') autoGender = 'male';
        if (memberType === 'mother') autoGender = 'female';

        if (autoGender) {
            wrap.style.display = 'none';
            genderEl.value = autoGender;
            genderEl.removeAttribute('required');
        } else {
            wrap.style.display = 'block';
            genderEl.required = true;
        }
    }

    function toggleMemberBaptismFields() {
        var baptizedCheckbox = document.getElementById('is_baptized');
        var wrap = document.getElementById('memberBaptismFields');
        if (!baptizedCheckbox) {
            return;
        }

        var checked = baptizedCheckbox.checked;
        if (form) {
            form.classList.toggle('wizard-is-baptized', checked);
        }
        if (wrap) {
            wrap.classList.toggle('is-visible', checked);
            wrap.style.display = '';
        }

        if (!checked) {
            ['baptism_date', 'baptism_place', 'baptized_by'].forEach(function (name) {
                var el = form.querySelector('[name="' + name + '"]');
                if (el) {
                    el.value = '';
                }
            });
        }
    }

    function ageFromDobValue(value) {
        if (!value) return null;
        var birth = new Date(value + 'T00:00:00');
        if (isNaN(birth.getTime())) return null;
        var today = new Date();
        var age = today.getFullYear() - birth.getFullYear();
        var m = today.getMonth() - birth.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
        return age;
    }

    function kipaimaraMinAge() {
        var min = parseInt(window.memberWizardConfig.kipaimaraMinAge, 10);
        return isNaN(min) ? 11 : min;
    }

    function kipaimaraFeatureEnabled() {
        return !!window.memberWizardConfig.kipaimaraRegistrationEnabled;
    }

    function youthMaxAge() {
        var max = parseInt(window.memberWizardConfig.youthMaxAge, 10);
        return isNaN(max) ? 21 : max;
    }

    function envelopeRequiredFromAge() {
        var from = parseInt(window.memberWizardConfig.envelopeRequiredFromAge, 10);
        return isNaN(from) ? (youthMaxAge() + 1) : from;
    }

    function envelopeRequiredForAge(age) {
        if (age === null) return true;
        return age > youthMaxAge();
    }

    function syncEnvelopeRequirement() {
        if (isSelfRegistration()) return;
        var input = document.getElementById('envelope_number');
        var mark = document.getElementById('envelopeRequiredMark');
        var hint = document.getElementById('envelopeAgeHint');
        var dob = form ? form.querySelector('[name="date_of_birth"]') : null;
        if (!input) return;

        var age = ageFromDobValue(dob ? dob.value : '');
        var required = envelopeRequiredForAge(age);
        if (required) {
            input.setAttribute('required', 'required');
        } else {
            input.removeAttribute('required');
        }
        if (mark) mark.style.display = required ? 'inline' : 'none';
        if (hint) {
            if (age !== null && !required) {
                hint.style.display = 'block';
                hint.textContent = 'Optional for ages up to ' + youthMaxAge() + '. Required from age ' + envelopeRequiredFromAge() + '.';
            } else if (age !== null && required) {
                hint.style.display = 'block';
                hint.textContent = 'Required from age ' + envelopeRequiredFromAge() + '.';
            } else {
                hint.style.display = 'none';
            }
        }
    }

    function syncMemberKipaimaraVisibility() {
        var section = document.getElementById('memberKipaimaraSection');
        var kipaimaraCheckbox = document.getElementById('is_kipaimara');
        if (!section || !kipaimaraFeatureEnabled()) {
            return;
        }

        var dob = form ? form.querySelector('[name="date_of_birth"]') : null;
        var age = ageFromDobValue(dob ? dob.value : '');
        var show = age === null || age >= kipaimaraMinAge();
        section.style.display = show ? 'block' : 'none';

        if (!show && kipaimaraCheckbox) {
            kipaimaraCheckbox.checked = false;
            toggleMemberKipaimaraFields();
        }
    }

    function syncDependantKipaimaraVisibility(row) {
        if (!row || !kipaimaraFeatureEnabled()) return;
        var wrap = row.querySelector('.dependant-kipaimara-wrap');
        var fields = row.querySelector('.dependant-kipaimara-fields');
        var checkbox = row.querySelector('.dependant-kipaimara');
        var dob = row.querySelector('[data-name="date_of_birth"], .dependant-dob, input[name*="[date_of_birth]"]');
        if (!wrap) return;

        var age = ageFromDobValue(dob ? dob.value : '');
        var show = age !== null && age >= kipaimaraMinAge();
        wrap.style.display = show ? 'block' : 'none';
        if (!show) {
            if (checkbox) checkbox.checked = false;
            if (fields) fields.style.display = 'none';
        }
    }

    function toggleMemberKipaimaraFields() {
        var kipaimaraCheckbox = document.getElementById('is_kipaimara');
        var wrap = document.getElementById('memberKipaimaraFields');
        if (!kipaimaraCheckbox) {
            return;
        }

        var checked = kipaimaraCheckbox.checked;
        if (form) {
            form.classList.toggle('wizard-is-kipaimara', checked);
        }
        if (wrap) {
            wrap.classList.toggle('is-visible', checked);
            wrap.style.display = '';
        }

        if (!checked) {
            ['kipaimara_date', 'kipaimara_place', 'kipaimara_by'].forEach(function (name) {
                var el = form.querySelector('[name="' + name + '"]');
                if (el) {
                    el.value = '';
                }
            });
        }
    }

    function prepareFormForSubmit() {
        var baptizedCheckbox = document.getElementById('is_baptized');
        if (baptizedCheckbox && !baptizedCheckbox.checked) {
            ['baptism_date', 'baptism_place', 'baptized_by'].forEach(function (name) {
                var el = form.querySelector('[name="' + name + '"]');
                if (el) {
                    el.value = '';
                }
            });
        }

        var kipaimaraCheckbox = document.getElementById('is_kipaimara');
        if (kipaimaraCheckbox && !kipaimaraCheckbox.checked) {
            ['kipaimara_date', 'kipaimara_place', 'kipaimara_by'].forEach(function (name) {
                var el = form.querySelector('[name="' + name + '"]');
                if (el) {
                    el.value = '';
                }
            });
        }

        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (el.disabled || isInHiddenContainer(el)) {
                el.removeAttribute('required');
                el.removeAttribute('pattern');
                el.removeAttribute('min');
                el.removeAttribute('max');
                el.removeAttribute('minlength');
                el.removeAttribute('maxlength');
            }
        });
    }

    function toggleSpouseSection() {
        var married = getField('marital_status') === 'married';
        var section = document.getElementById('spouseSection');
        var weddingSection = document.getElementById('weddingSection');
        var editSpouseInfo = document.getElementById('editSpouseInfo');
        if (section) section.style.display = married ? 'block' : 'none';
        if (weddingSection) weddingSection.style.display = married ? 'block' : 'none';
        if (editSpouseInfo) editSpouseInfo.style.display = married ? 'block' : 'none';
        toggleWeddingFields(married);
        toggleSpouseMemberFields();
    }

    function toggleWeddingFields(married) {
        var weddingType = document.getElementById('wedding_type');
        var weddingDate = document.getElementById('wedding_date');
        if (weddingType) {
            weddingType.required = married;
            weddingType.disabled = !married;
        }
        if (weddingDate) {
            weddingDate.required = false;
            weddingDate.disabled = !married;
        }
    }

    function setSpouseSectionActive(active) {
        var spouseSection = document.getElementById('spouseSection');
        if (!spouseSection) return;

        spouseSection.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (!active) {
                el.disabled = true;
                el.removeAttribute('required');
            }
        });

        if (active) {
            var churchMember = document.getElementById('spouse_church_member');
            var inputMethod = document.getElementById('spouse_input_method');
            if (churchMember) churchMember.disabled = false;
            if (inputMethod) inputMethod.disabled = false;
        }
    }

    function setFieldEnabled(container, enabled) {
        if (!container) return;
        container.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (el.id === 'spouse_input_method' || el.id === 'spouse_church_member') return;
            el.disabled = !enabled;
            if (!enabled) {
                el.removeAttribute('required');
            }
        });
    }

    function isSelfRegistration() {
        return !!(window.memberWizardConfig && window.memberWizardConfig.isSelfRegistration);
    }

    function syncSpouseEnvelopeFieldName() {
        if (isSelfRegistration()) {
            return;
        }

        var selectField = document.getElementById('spouse_envelope_number_select');
        var manualField = document.getElementById('spouse_envelope_number_manual');
        var isMember = getField('spouse_church_member') === 'yes';
        var method = getField('spouse_input_method');
        var useSelect = isMember && method === 'select';
        var useManual = !isMember || (isMember && method === 'manual');

        if (selectField) {
            if (useSelect) {
                selectField.setAttribute('name', 'spouse_envelope_number');
            } else {
                selectField.removeAttribute('name');
            }
        }
        if (manualField) {
            if (useManual) {
                manualField.setAttribute('name', 'spouse_envelope_number');
                manualField.required = true;
            } else {
                manualField.removeAttribute('name');
                manualField.removeAttribute('required');
            }
        }
    }

    function toggleSpouseMemberFields() {
        var married = getField('marital_status') === 'married';

        if (!married) {
            setSpouseSectionActive(false);
            syncSpouseEnvelopeFieldName();
            return;
        }

        setSpouseSectionActive(true);

        var isMember = getField('spouse_church_member') === 'yes';
        var method = getField('spouse_input_method');
        var methodWrap = document.getElementById('spouseInputMethodWrap');
        var memberSelect = document.getElementById('spouseMemberSelect');
        var manualFields = document.getElementById('spouseManualFields');
        var envelopeManualWrap = document.getElementById('spouseEnvelopeManualWrap');
        var selfReg = isSelfRegistration();

        if (methodWrap) methodWrap.style.display = isMember ? 'block' : 'none';

        var useSelect = !selfReg && isMember && method === 'select';
        var useManual = selfReg ? true : (!isMember || (isMember && method === 'manual'));

        if (memberSelect) memberSelect.style.display = useSelect ? 'block' : 'none';
        if (manualFields) manualFields.style.display = useManual ? 'block' : 'none';
        if (envelopeManualWrap) envelopeManualWrap.style.display = (!selfReg && useManual) ? 'block' : 'none';

        setFieldEnabled(memberSelect, useSelect);
        setFieldEnabled(manualFields, useManual);
        syncSpouseEnvelopeFieldName();

        if (useManual) {
            var memberId = document.getElementById('spouse_member_id');
            if (memberId) memberId.value = '';
        }

        if (useSelect) {
            var memberIdEl = document.getElementById('spouse_member_id');
            if (memberIdEl) {
                memberIdEl.disabled = false;
                memberIdEl.required = true;
            }
            ['spouse_full_name', 'spouse_gender', 'spouse_date_of_birth', 'spouse_phone_number', 'spouse_email'].forEach(function (name) {
                var el = form.querySelector('[name="' + name + '"]');
                if (el) el.removeAttribute('required');
            });
        } else if (useManual) {
            var memberIdEl = document.getElementById('spouse_member_id');
            if (memberIdEl) {
                memberIdEl.removeAttribute('required');
                memberIdEl.value = '';
            }
            var nameEl = form.querySelector('[name="spouse_full_name"]');
            var genderEl = form.querySelector('[name="spouse_gender"]');
            var dobEl = form.querySelector('[name="spouse_date_of_birth"]');
            if (nameEl) { nameEl.disabled = false; nameEl.required = true; }
            if (genderEl) { genderEl.disabled = false; genderEl.required = true; }
            if (dobEl) { dobEl.disabled = false; dobEl.required = true; }
        } else {
            ['spouse_full_name', 'spouse_gender', 'spouse_date_of_birth', 'spouse_phone_number', 'spouse_email', 'spouse_member_id'].forEach(function (name) {
                var el = form.querySelector('[name="' + name + '"]');
                if (el) el.removeAttribute('required');
            });
        }

        syncSpouseEnvelopeFieldName();
    }

    function suggestSpouseGender() {
        toggleGenderField();
        var memberType = getField('member_type');
        var spouseGender = document.getElementById('spouse_gender');
        if (!spouseGender) return;
        if (memberType === 'father') spouseGender.value = 'female';
        else if (memberType === 'mother') spouseGender.value = 'male';
    }

    function checkEnvelope() {
        var input = document.getElementById('envelope_number');
        var status = document.getElementById('envelope_status');
        if (!input || !status || !window.memberWizardConfig.checkEnvelopeUrl || input.value.length !== 3) return;

        var url = window.memberWizardConfig.checkEnvelopeUrl + '?envelope=' + encodeURIComponent(input.value);
        if (window.memberWizardConfig.memberId) {
            url += '&except=' + encodeURIComponent(window.memberWizardConfig.memberId);
        }

        var branchField = document.querySelector('[name="branch_id"]');
        var branchId = branchField ? branchField.value : (window.memberWizardConfig.branchId || '');
        if (branchId) {
            url += '&branch_id=' + encodeURIComponent(branchId);
        }

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                status.textContent = data.message;
                status.className = 'envelope-status ' + (data.available ? 'ok' : 'error');
            });
    }

    var addDependantBtn = document.getElementById('addDependantBtn');
    if (addDependantBtn) {
        addDependantBtn.addEventListener('click', function () {
            var template = document.getElementById('dependantTemplate');
            var clone = template.content.cloneNode(true);
            var container = document.getElementById('dependantsContainer');
            container.appendChild(clone);
            syncDependantNames();
            var newRow = container.querySelector('.dependant-row:last-child');
            if (newRow && typeof window.bindDependantSchoolLocation === 'function') {
                window.bindDependantSchoolLocation(newRow);
            }
            if (newRow) {
                syncDependantKipaimaraVisibility(newRow);
            }
            dependantIndex++;
        });
    }

    var dependantsContainer = document.getElementById('dependantsContainer');
    if (dependantsContainer) {
        dependantsContainer.addEventListener('click', function (e) {
            if (e.target.closest('.remove-dependant')) {
                e.target.closest('.dependant-row').remove();
                syncDependantNames();
            }
        });

        dependantsContainer.addEventListener('change', function (e) {
            if (e.target.classList.contains('dependant-baptized')) {
                var row = e.target.closest('.dependant-row');
                var fields = row ? row.querySelector('.dependant-baptism-fields') : null;
                if (fields) {
                    fields.style.display = e.target.checked ? 'block' : 'none';
                }
            }
            if (e.target.classList.contains('dependant-kipaimara')) {
                var kipaimaraRow = e.target.closest('.dependant-row');
                var kipaimaraFields = kipaimaraRow ? kipaimaraRow.querySelector('.dependant-kipaimara-fields') : null;
                if (kipaimaraFields) {
                    kipaimaraFields.style.display = e.target.checked ? 'block' : 'none';
                }
            }
            if (e.target.matches('[data-name="date_of_birth"], .dependant-dob') || (e.target.name && e.target.name.indexOf('[date_of_birth]') !== -1)) {
                syncDependantKipaimaraVisibility(e.target.closest('.dependant-row'));
            }
            if (e.target.classList.contains('dependant-is-student')) {
                var studentRow = e.target.closest('.dependant-row');
                var educationFields = studentRow ? studentRow.querySelector('.dependant-education-fields') : null;
                if (educationFields) {
                    educationFields.style.display = e.target.checked ? 'block' : 'none';
                }
                if (!e.target.checked && studentRow) {
                    var level = studentRow.querySelector('.dependant-education-level');
                    var schoolName = studentRow.querySelector('.dependant-school-name');
                    var ward = studentRow.querySelector('.dependant-school-ward');
                    var street = studentRow.querySelector('.dependant-school-street');
                    var schoolRegion = studentRow.querySelector('.dependant-school-region');
                    var schoolDistrict = studentRow.querySelector('.dependant-school-district');
                    if (level) level.value = '';
                    if (schoolName) schoolName.value = '';
                    if (ward) ward.value = '';
                    if (street) street.value = '';
                    if (schoolRegion) schoolRegion.value = '';
                    if (schoolDistrict) {
                        schoolDistrict.innerHTML = '<option value="">' + wizardI18n('locations.select_region_first', 'Select region first') + '</option>';
                        schoolDistrict.disabled = true;
                    }
                } else if (studentRow && typeof window.bindDependantSchoolLocation === 'function') {
                    window.bindDependantSchoolLocation(studentRow);
                }
            }
        });
    }

    var memberBaptized = document.getElementById('is_baptized');
    if (memberBaptized) {
        memberBaptized.addEventListener('change', toggleMemberBaptismFields);
        toggleMemberBaptismFields();
    }

    var memberKipaimara = document.getElementById('is_kipaimara');
    if (memberKipaimara) {
        memberKipaimara.addEventListener('change', toggleMemberKipaimaraFields);
        toggleMemberKipaimaraFields();
    }

    var memberDob = form ? form.querySelector('[name="date_of_birth"]') : null;
    if (memberDob) {
        memberDob.addEventListener('change', function () {
            syncMemberKipaimaraVisibility();
            syncEnvelopeRequirement();
        });
        memberDob.addEventListener('input', function () {
            syncMemberKipaimaraVisibility();
            syncEnvelopeRequirement();
        });
        syncMemberKipaimaraVisibility();
        syncEnvelopeRequirement();
    }

    if (dependantsContainer) {
        dependantsContainer.querySelectorAll('.dependant-row').forEach(syncDependantKipaimaraVisibility);
        dependantsContainer.addEventListener('input', function (e) {
            if (e.target.matches('[data-name="date_of_birth"], .dependant-dob') || (e.target.name && e.target.name.indexOf('[date_of_birth]') !== -1)) {
                syncDependantKipaimaraVisibility(e.target.closest('.dependant-row'));
            }
        });
    }

    form.setAttribute('novalidate', 'novalidate');

    var formSubmitting = false;

    nextBtn.addEventListener('click', function () {
        if (!validateStep(currentStep, false)) return;
        if (currentStep < totalSteps) showStep(currentStep + 1);
    });

    prevBtn.addEventListener('click', function () {
        if (currentStep > 1) showStep(currentStep - 1);
    });

    form.addEventListener('submit', function (e) {
        if (formSubmitting) {
            return;
        }

        e.preventDefault();
        prepareFormForSubmit();

        for (var step = 1; step <= totalSteps; step++) {
            if (!validateStep(step, true)) {
                return;
            }
        }

        syncDependantNames();
        syncSpouseEnvelopeFieldName();
        formatPhoneForSubmit();
        formSubmitting = true;
        form.submit();
    });

    var maritalStatus = document.getElementById('marital_status');
    if (maritalStatus) maritalStatus.addEventListener('change', toggleSpouseSection);

    var spouseChurchMember = document.getElementById('spouse_church_member');
    if (spouseChurchMember) spouseChurchMember.addEventListener('change', toggleSpouseMemberFields);

    var spouseInputMethod = document.getElementById('spouse_input_method');
    if (spouseInputMethod) spouseInputMethod.addEventListener('change', toggleSpouseMemberFields);

    var membershipType = document.getElementById('membership_type');
    if (membershipType) membershipType.addEventListener('change', toggleMemberType);

    var memberType = document.getElementById('member_type');
    if (memberType) {
        memberType.addEventListener('change', function () {
            suggestSpouseGender();
            toggleIndependentFamilySection();
        });
    }

    var familyParentType = document.getElementById('family_parent_type');
    if (familyParentType) familyParentType.addEventListener('change', toggleIndependentFamilyFields);

    var familyMemberSelect = document.getElementById('family_member_id');
    if (familyMemberSelect) familyMemberSelect.addEventListener('change', updateSecondaryFamilyHint);

    var envelopeNumber = document.getElementById('envelope_number');
    if (envelopeNumber) envelopeNumber.addEventListener('blur', checkEnvelope);

    var branchSelect = document.querySelector('select[name="branch_id"]');
    if (branchSelect) {
        branchSelect.addEventListener('change', function () {
            window.memberWizardConfig.branchId = branchSelect.value || null;
            checkEnvelope();
        });
    }

    var spouseMemberId = document.getElementById('spouse_member_id');
    if (spouseMemberId) {
        spouseMemberId.addEventListener('change', function () {
            var option = this.options[this.selectedIndex];
            var envelope = option ? option.getAttribute('data-envelope') : '';
            var field = document.getElementById('spouse_envelope_number_select');
            if (field && envelope) field.value = envelope;
        });
    }

    var profilePicture = document.getElementById('profile_picture');
    if (profilePicture) {
        profilePicture.addEventListener('change', function (e) {
            var preview = document.getElementById('profile_preview');
            if (e.target.files && e.target.files[0]) {
                preview.src = URL.createObjectURL(e.target.files[0]);
                preview.style.display = 'block';
            }
        });
    }

    var tribeSelect = document.getElementById('tribe');
    if (tribeSelect) {
        tribeSelect.addEventListener('change', function () {
            toggleOtherTribe('tribe', 'otherTribeWrap', 'other_tribe');
        });
        toggleOtherTribe('tribe', 'otherTribeWrap', 'other_tribe');
    }

    var spouseTribeSelect = document.getElementById('spouse_tribe');
    if (spouseTribeSelect) {
        spouseTribeSelect.addEventListener('change', function () {
            toggleOtherTribe('spouse_tribe', 'spouseOtherTribeWrap', 'spouse_other_tribe');
        });
        toggleOtherTribe('spouse_tribe', 'spouseOtherTribeWrap', 'spouse_other_tribe');
    }

    toggleMemberType();
    toggleSpouseSection();
    initTanzaniaLocations();
    showStep(1);
})();
