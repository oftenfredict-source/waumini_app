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
        if (step === totalSteps) buildSummary();
        window.scrollTo({ top: 0, behavior: 'smooth' });
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

    function initTanzaniaLocations() {
        var regionEl = document.getElementById('region');
        var districtEl = document.getElementById('district');
        var residenceRegionEl = document.getElementById('residence_region');
        var residenceDistrictEl = document.getElementById('residence_district');
        var locationsUrl = window.memberWizardConfig && window.memberWizardConfig.locationsUrl;

        if (!regionEl || !districtEl || !locationsUrl) return;

        function populateRegions(selectEl, selected) {
            selectEl.innerHTML = '<option value="">Select region</option>';
            regions.forEach(function (region) {
                var option = document.createElement('option');
                option.value = region.name;
                option.textContent = region.name;
                if (region.name === selected) option.selected = true;
                selectEl.appendChild(option);
            });
        }

        function populateDistricts(regionName, districtSelect, selectedDistrict) {
            var region = regions.find(function (item) { return item.name === regionName; });
            var districts = region ? region.districts : [];

            districtSelect.innerHTML = '<option value="">Select district</option>';
            districts.forEach(function (district) {
                var option = document.createElement('option');
                option.value = district.name;
                option.textContent = district.name;
                if (district.name === selectedDistrict) option.selected = true;
                districtSelect.appendChild(option);
            });

            districtSelect.disabled = !regionName;
        }

        var savedRegion = regionEl.getAttribute('data-selected') || '';
        var savedDistrict = districtEl.getAttribute('data-selected') || '';
        var savedResidenceRegion = residenceRegionEl ? residenceRegionEl.getAttribute('data-selected') || '' : '';
        var savedResidenceDistrict = residenceDistrictEl ? residenceDistrictEl.getAttribute('data-selected') || '' : '';
        var regions = [];

        fetch(locationsUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                regions = data.regions || [];
                populateRegions(regionEl, savedRegion);
                if (residenceRegionEl) populateRegions(residenceRegionEl, savedResidenceRegion);

                if (savedRegion) populateDistricts(savedRegion, districtEl, savedDistrict);
                if (residenceRegionEl && savedResidenceRegion) {
                    populateDistricts(savedResidenceRegion, residenceDistrictEl, savedResidenceDistrict);
                }

                regionEl.addEventListener('change', function () {
                    populateDistricts(this.value, districtEl, '');
                });

                if (residenceRegionEl && residenceDistrictEl) {
                    residenceRegionEl.addEventListener('change', function () {
                        populateDistricts(this.value, residenceDistrictEl, '');
                    });
                }
            })
            .catch(function () {
                regionEl.innerHTML = '<option value="">Unable to load regions</option>';
                if (residenceRegionEl) residenceRegionEl.innerHTML = '<option value="">Unable to load regions</option>';
            });
    }

    function validateStep(step) {
        var panel = form.querySelector('.wizard-panel[data-step="' + step + '"]');
        var inputs = panel.querySelectorAll('input, select, textarea');
        for (var i = 0; i < inputs.length; i++) {
            var input = inputs[i];
            if (input.offsetParent === null || input.disabled) continue;
            if (!input.checkValidity()) {
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
        var html = '<div class="row">';
        html += summaryBlock('Personal Information', [
            ['Membership Type', getSelectText('membership_type')],
            ['Stay Duration', getField('membership_type') === 'temporary'
                ? (getField('temporary_duration_value') + ' ' + getSelectText('temporary_duration_unit'))
                : ''],
            ['Member Type', getSelectText('member_type')],
            ['Envelope Number', getField('envelope_number')],
            ['Full Name', getField('full_name')],
            ['Gender', getSelectText('gender')],
            ['Date of Birth', getField('date_of_birth')],
            ['Education Level', getSelectText('education_level')],
            ['Profession', getField('profession')],
            ['NIDA Number', getField('nida_number')],
            ['Baptized', document.getElementById('is_baptized')?.checked ? 'Yes' : 'No'],
            ['Baptism Date', document.getElementById('is_baptized')?.checked ? getField('baptism_date') : ''],
            ['Baptism Place', document.getElementById('is_baptized')?.checked ? getField('baptism_place') : ''],
            ['Baptized By', document.getElementById('is_baptized')?.checked ? getField('baptized_by') : ''],
        ]);
        html += summaryBlock('Contact & Origin', [
            ['Phone', formatPhoneDisplay()],
            ['Email', getField('email')],
            ['Origin Region', getSelectText('region')],
            ['District', getSelectText('district')],
            ['Ward', getField('ward')],
            ['Street', getField('street')],
            ['P.O. Box', getField('po_box')],
            ['Tribe', getTribeDisplay('tribe', 'other_tribe')],
        ]);
        html += summaryBlock('Residence', [
            ['Region', getSelectText('residence_region')],
            ['District', getSelectText('residence_district')],
            ['Ward', getField('residence_ward')],
            ['Street', getField('residence_street')],
            ['Road', getField('residence_road')],
            ['House Number', getField('residence_house_number')],
        ]);
        html += summaryBlock('Family Information', (function () {
            var rows = [
                ['Marital Status', getSelectText('marital_status')],
            ];
            if (getField('marital_status') === 'married') {
                rows.push(['Wedding Type', getSelectText('wedding_type')]);
                rows.push(['Wedding Date', getField('wedding_date')]);
                rows.push(['Spouse Church Member', getSelectText('spouse_church_member')]);
                if (getField('spouse_church_member') === 'yes') {
                    rows.push(['Spouse Entry Method', getSelectText('spouse_input_method')]);
                    if (getField('spouse_input_method') === 'select') {
                        rows.push(['Spouse (Selected)', getSelectText('spouse_member_id')]);
                        rows.push(['Spouse Envelope', document.getElementById('spouse_envelope_number_select')?.value]);
                    } else {
                        rows.push(['Spouse Name', getField('spouse_full_name')]);
                        rows.push(['Spouse Envelope', document.getElementById('spouse_envelope_number_manual')?.value]);
                        rows.push(['Spouse Gender', getSelectText('spouse_gender')]);
                        rows.push(['Spouse DOB', getField('spouse_date_of_birth')]);
                        rows.push(['Spouse Phone', formatSpousePhoneDisplay()]);
                        rows.push(['Spouse Tribe', getTribeDisplay('spouse_tribe', 'spouse_other_tribe')]);
                    }
                } else {
                    rows.push(['Spouse Name', getField('spouse_full_name')]);
                    rows.push(['Spouse Gender', getSelectText('spouse_gender')]);
                    rows.push(['Spouse DOB', getField('spouse_date_of_birth')]);
                    rows.push(['Spouse Phone', formatSpousePhoneDisplay()]);
                    rows.push(['Spouse Tribe', getTribeDisplay('spouse_tribe', 'spouse_other_tribe')]);
                }
            }
            if (window.memberWizardConfig && window.memberWizardConfig.isEdit) {
                var notes = getField('notes');
                if (notes) {
                    rows.push(['Notes', notes]);
                }
            }
            return rows;
        })());
        html += '</div>';

        var dependants = document.querySelectorAll('#dependantsContainer .dependant-row');
        if (dependants.length) {
            html += '<h5 class="mt-3">Dependants</h5><ul class="list-group">';
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

    function prepareFormForSubmit() {
        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (el.offsetParent === null || el.disabled) {
                el.removeAttribute('required');
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

    function syncSpouseEnvelopeFieldName() {
        var selectField = document.getElementById('spouse_envelope_number_select');
        var manualField = document.getElementById('spouse_envelope_number_manual');
        var isMember = getField('spouse_church_member') === 'yes';
        var method = getField('spouse_input_method');
        var useSelect = isMember && method === 'select';

        if (selectField) {
            if (useSelect) {
                selectField.setAttribute('name', 'spouse_envelope_number');
            } else {
                selectField.removeAttribute('name');
            }
        }
        if (manualField) {
            if (isMember && method === 'manual') {
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

        if (methodWrap) methodWrap.style.display = isMember ? 'block' : 'none';

        var useSelect = isMember && method === 'select';
        var useManual = !isMember || (isMember && method === 'manual');

        if (memberSelect) memberSelect.style.display = useSelect ? 'block' : 'none';
        if (manualFields) manualFields.style.display = useManual ? 'block' : 'none';
        if (envelopeManualWrap) envelopeManualWrap.style.display = (isMember && method === 'manual') ? 'block' : 'none';

        setFieldEnabled(memberSelect, useSelect);
        setFieldEnabled(manualFields, useManual);

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
        if (!input || !status || input.value.length !== 3) return;

        var url = window.memberWizardConfig.checkEnvelopeUrl + '?envelope=' + encodeURIComponent(input.value);
        if (window.memberWizardConfig.memberId) {
            url += '&except=' + encodeURIComponent(window.memberWizardConfig.memberId);
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
            document.getElementById('dependantsContainer').appendChild(clone);
            syncDependantNames();
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
        });
    }

    var memberBaptized = document.getElementById('is_baptized');
    if (memberBaptized) {
        memberBaptized.addEventListener('change', function () {
            var wrap = document.getElementById('memberBaptismFields');
            if (wrap) {
                wrap.style.display = this.checked ? 'block' : 'none';
            }
        });
    }

    nextBtn.addEventListener('click', function () {
        if (!validateStep(currentStep)) return;
        if (currentStep < totalSteps) showStep(currentStep + 1);
    });

    prevBtn.addEventListener('click', function () {
        if (currentStep > 1) showStep(currentStep - 1);
    });

    form.addEventListener('submit', function () {
        prepareFormForSubmit();
        syncDependantNames();
        syncSpouseEnvelopeFieldName();
        formatPhoneForSubmit();
    });

    submitBtn.addEventListener('click', function () {
        prepareFormForSubmit();
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
    if (memberType) memberType.addEventListener('change', suggestSpouseGender);

    var envelopeNumber = document.getElementById('envelope_number');
    if (envelopeNumber) envelopeNumber.addEventListener('blur', checkEnvelope);

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
