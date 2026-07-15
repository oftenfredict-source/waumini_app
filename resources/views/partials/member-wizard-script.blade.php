@php
    $wizardJsVersion = @filemtime(public_path('js/member-wizard.js')) ?: time();
@endphp
<script src="{{ \App\Support\WauminiBrand::publicAsset('js/member-wizard.js') }}?v={{ $wizardJsVersion }}"></script>
<script>
(function () {
    var form = document.getElementById('memberWizardForm');
    if (!form) return;

    function syncIndependentUi() {
        var membership = form.querySelector('[name="membership_type"]');
        var memberType = form.querySelector('[name="member_type"]');
        var familyType = form.querySelector('[name="family_parent_type"]');
        var show = membership && memberType
            && membership.value === 'permanent'
            && memberType.value === 'independent';
        var familyMode = (familyType && familyType.value) ? familyType.value : 'member';

        form.classList.toggle('wizard-is-independent', !!show);
        form.classList.toggle('wizard-family-guardian', !!show && familyMode === 'guardian');
        form.classList.toggle('wizard-family-member', !!show && familyMode !== 'guardian');

        var section = document.getElementById('independentFamilySection');
        var note = document.getElementById('independentMaritalNote');
        var marital = document.getElementById('maritalStatusSection');
        var dependants = document.getElementById('dependantsSection');
        var hint = document.getElementById('independentMemberTypeHint');
        var memberSection = document.getElementById('independentFamilyMemberSection');
        var guardianSection = document.getElementById('independentGuardianSection');
        var familyMember = document.getElementById('family_member_id');
        var guardianName = document.getElementById('guardian_full_name');
        var relationship = document.getElementById('guardian_relationship');
        var guardianPhone = document.getElementById('guardian_phone');

        if (section) {
            section.classList.toggle('is-visible', !!show);
            if (show) {
                section.removeAttribute('hidden');
            } else {
                section.setAttribute('hidden', 'hidden');
            }
            section.style.display = '';
        }
        if (note) note.style.display = '';
        if (marital) marital.style.display = '';
        if (dependants) dependants.style.display = '';
        if (hint) hint.style.display = (membership && membership.value === 'permanent') ? 'block' : 'none';

        if (!show) {
            if (familyType) familyType.removeAttribute('required');
            if (familyMember) familyMember.removeAttribute('required');
            if (guardianName) guardianName.removeAttribute('required');
            if (relationship) relationship.removeAttribute('required');
            return;
        }

        if (familyType) familyType.required = true;
        if (relationship) relationship.required = true;

        if (memberSection) {
            memberSection.style.display = familyMode === 'member' ? '' : 'none';
            memberSection.hidden = familyMode !== 'member';
        }
        if (guardianSection) {
            guardianSection.style.display = familyMode === 'guardian' ? '' : 'none';
            guardianSection.hidden = familyMode !== 'guardian';
        }

        if (familyMember) {
            if (familyMode === 'member') {
                familyMember.required = true;
            } else {
                familyMember.removeAttribute('required');
                familyMember.value = '';
            }
        }

        if (guardianName) {
            if (familyMode === 'guardian') {
                guardianName.required = true;
            } else {
                guardianName.removeAttribute('required');
                guardianName.value = '';
                if (guardianPhone) guardianPhone.value = '';
            }
        }
    }

    ['membership_type', 'member_type', 'family_parent_type'].forEach(function (name) {
        var el = form.querySelector('[name="' + name + '"]');
        if (el) el.addEventListener('change', syncIndependentUi);
    });

    syncIndependentUi();
    document.addEventListener('DOMContentLoaded', syncIndependentUi);
})();
</script>
