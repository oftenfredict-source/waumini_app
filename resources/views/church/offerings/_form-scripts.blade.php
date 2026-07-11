<script>
(function () {
    var typeSelect = document.getElementById('offering_type');
    var otherGroup = document.getElementById('offeringTypeOtherGroup');
    var otherInput = document.getElementById('offering_type_other');
    var memberGroup = document.getElementById('memberSelectionGroup');
    var serviceGroup = document.getElementById('serviceSelectionGroup');
    var memberIdInput = document.getElementById('member_id');
    var memberSearch = document.getElementById('member_envelope_search');
    var memberResults = document.getElementById('member_envelope_results');
    var serviceSelect = document.getElementById('church_service_id');
    var offeringDate = document.getElementById('offering_date');
    var helpMember = document.getElementById('contributionHelpMember');
    var helpGeneral = document.getElementById('contributionHelpGeneral');
    var contributionToggle = document.getElementById('contributionTypeToggle');
    var serviceOptions = serviceSelect ? Array.from(serviceSelect.querySelectorAll('option[data-service-date]')) : [];
    var noMembersFound = @json(__('pages.offerings.no_envelope_match'));
    var members = [];

    try {
        var dataEl = document.getElementById('offeringMembersData');
        members = dataEl ? JSON.parse(dataEl.textContent || '[]') : [];
    } catch (e) {
        members = [];
    }

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

    function setFieldEnabled(field, enabled) {
        if (!field) {
            return;
        }
        field.disabled = !enabled;
        if (!enabled) {
            field.value = '';
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

    function memberLabel(member) {
        if (member.envelope) {
            return member.envelope + ' — ' + member.name;
        }
        return member.name;
    }

    function hideMemberResults() {
        if (!memberResults) {
            return;
        }
        memberResults.style.display = 'none';
        memberResults.innerHTML = '';
    }

    function renderMemberResults(items) {
        if (!memberResults) {
            return;
        }
        memberResults.innerHTML = '';

        if (!items.length) {
            memberResults.innerHTML = '<div class="list-group-item text-muted">' + noMembersFound + '</div>';
            memberResults.style.display = 'block';
            return;
        }

        items.forEach(function (member) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'list-group-item list-group-item-action';

            if (member.envelope) {
                var strong = document.createElement('strong');
                strong.className = 'text-primary';
                strong.textContent = member.envelope;
                btn.appendChild(strong);
                btn.appendChild(document.createTextNode(' — ' + member.name));
            } else {
                btn.textContent = member.name;
            }

            btn.addEventListener('click', function () {
                if (memberIdInput) {
                    memberIdInput.value = member.id;
                }
                if (memberSearch) {
                    memberSearch.value = memberLabel(member);
                }
                hideMemberResults();
            });
            memberResults.appendChild(btn);
        });

        memberResults.style.display = 'block';
    }

    function searchMembersByEnvelope(term) {
        var q = (term || '').toLowerCase().trim();
        if (!q) {
            return [];
        }

        return members.filter(function (member) {
            var envelope = (member.envelope || '').toLowerCase();
            return envelope.indexOf(q) !== -1;
        }).slice(0, 25);
    }

    function bindEnvelopeSearch() {
        if (!memberSearch || !memberResults || !memberIdInput) {
            return;
        }

        memberSearch.addEventListener('input', function () {
            memberIdInput.value = '';
            var q = memberSearch.value.trim();
            if (!q) {
                hideMemberResults();
                return;
            }
            renderMemberResults(searchMembersByEnvelope(q));
        });

        memberSearch.addEventListener('focus', function () {
            var q = memberSearch.value.trim();
            if (q && !memberIdInput.value) {
                renderMemberResults(searchMembersByEnvelope(q));
            }
        });

        document.addEventListener('click', function (event) {
            if (!memberGroup || memberGroup.contains(event.target)) {
                return;
            }
            hideMemberResults();
        });
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

        setFieldEnabled(memberIdInput, isMember);
        setFieldEnabled(memberSearch, isMember);
        setFieldEnabled(serviceSelect, isGeneral);

        if (serviceSelect) {
            serviceSelect.required = isGeneral;
        }

        if (!isMember) {
            hideMemberResults();
        }

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

    bindEnvelopeSearch();
    toggleContributionSections();
})();
</script>
