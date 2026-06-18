<script>
(function () {
    var typeSelect = document.getElementById('offering_type');
    var otherGroup = document.getElementById('offeringTypeOtherGroup');
    var otherInput = document.getElementById('offering_type_other');
    var memberGroup = document.getElementById('memberSelectionGroup');
    var serviceGroup = document.getElementById('serviceSelectionGroup');
    var memberSelect = document.getElementById('member_id');
    var serviceSelect = document.getElementById('church_service_id');
    var offeringDate = document.getElementById('offering_date');
    var helpMember = document.getElementById('contributionHelpMember');
    var helpGeneral = document.getElementById('contributionHelpGeneral');
    var contributionToggle = document.getElementById('contributionTypeToggle');
    var serviceOptions = serviceSelect ? Array.from(serviceSelect.querySelectorAll('option[data-service-date]')) : [];

    function selectedContributionType() {
        var checked = document.querySelector('input[name="contribution_type"]:checked');
        return checked ? checked.value : 'member';
    }

    function toggleOfferingTypeOther() {
        var isOther = typeSelect && typeSelect.value === 'other';
        if (otherGroup) {
            otherGroup.style.display = isOther ? 'block' : 'none';
        }
        if (otherInput) {
            otherInput.required = isOther;
        }
    }

    function setFieldEnabled(select, enabled) {
        if (!select) {
            return;
        }
        select.disabled = !enabled;
        select.required = enabled;
        if (!enabled) {
            select.value = '';
        }
    }

    function resetServiceOptions() {
        serviceOptions.forEach(function (option) {
            option.hidden = false;
            option.disabled = false;
        });
    }

    function suggestServiceForDate() {
        if (!serviceSelect || selectedContributionType() !== 'general' || serviceSelect.disabled) {
            return;
        }

        resetServiceOptions();

        var date = offeringDate ? offeringDate.value : '';
        if (!date || serviceSelect.value) {
            return;
        }

        var matchForDate = serviceOptions.find(function (option) {
            return option.getAttribute('data-service-date') === date;
        });

        if (matchForDate) {
            serviceSelect.value = matchForDate.value;
        }
    }

    function toggleContributionSections() {
        var isMember = selectedContributionType() === 'member';
        var isGeneral = selectedContributionType() === 'general';

        if (memberGroup) {
            memberGroup.style.display = isMember ? '' : 'none';
        }
        if (serviceGroup) {
            serviceGroup.style.display = isGeneral ? '' : 'none';
        }
        if (helpMember) {
            helpMember.style.display = isMember ? '' : 'none';
        }
        if (helpGeneral) {
            helpGeneral.style.display = isGeneral ? '' : 'none';
        }

        setFieldEnabled(memberSelect, isMember);
        setFieldEnabled(serviceSelect, isGeneral);

        if (isGeneral) {
            resetServiceOptions();
            suggestServiceForDate();
        }
    }

    if (typeSelect) {
        typeSelect.addEventListener('change', toggleOfferingTypeOther);
        toggleOfferingTypeOther();
    }

    document.querySelectorAll('input[name="contribution_type"]').forEach(function (radio) {
        radio.addEventListener('change', toggleContributionSections);
        radio.addEventListener('click', toggleContributionSections);
    });

    if (contributionToggle) {
        contributionToggle.addEventListener('click', function () {
            window.setTimeout(toggleContributionSections, 0);
        });
    }

    if (offeringDate) {
        offeringDate.addEventListener('change', suggestServiceForDate);
    }

    if (serviceSelect) {
        serviceSelect.addEventListener('change', function () {
            var selected = serviceSelect.selectedOptions[0];
            var serviceDate = selected ? selected.getAttribute('data-service-date') : null;
            if (serviceDate && offeringDate && !offeringDate.value) {
                offeringDate.value = serviceDate;
            }
        });
    }

    toggleContributionSections();
})();
</script>
