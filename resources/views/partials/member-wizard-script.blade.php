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
        var show = membership && memberType
            && membership.value === 'permanent'
            && memberType.value === 'independent';

        form.classList.toggle('wizard-is-independent', !!show);

        var section = document.getElementById('independentFamilySection');
        var note = document.getElementById('independentMaritalNote');
        var marital = document.getElementById('maritalStatusSection');
        var dependants = document.getElementById('dependantsSection');
        var hint = document.getElementById('independentMemberTypeHint');

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
    }

    ['membership_type', 'member_type'].forEach(function (name) {
        var el = form.querySelector('[name="' + name + '"]');
        if (el) el.addEventListener('change', syncIndependentUi);
    });

    syncIndependentUi();
    document.addEventListener('DOMContentLoaded', syncIndependentUi);
})();
</script>
