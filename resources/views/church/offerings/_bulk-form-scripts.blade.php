<script>
document.addEventListener('DOMContentLoaded', function () {
    var paymentSelect = document.getElementById('payment_method');
    var typeSelect = document.getElementById('offering_type');
    var otherGroup = document.getElementById('offeringTypeOtherGroup');
    var otherInput = document.getElementById('offering_type_other');
    var memberBulkGroup = document.getElementById('memberBulkGroup');
    var generalGroup = document.getElementById('generalOfferingGroup');
    var entriesContainer = document.getElementById('offeringEntries');
    var addBtn = document.getElementById('addOfferingEntry');
    var template = document.getElementById('offeringEntryTemplate');
    var membersDataEl = document.getElementById('offeringMembersData');
    var serviceSelect = document.getElementById('church_service_id');
    var generalAmount = document.getElementById('general_amount');
    var generalReference = document.getElementById('general_reference');
    var generalReferenceGroup = document.getElementById('generalReferenceGroup');
    var offeringDate = document.getElementById('offering_date');
    var helpMember = document.getElementById('contributionHelpMember');
    var helpGeneral = document.getElementById('contributionHelpGeneral');
    var form = memberBulkGroup ? memberBulkGroup.closest('form') : null;
    var serviceOptions = serviceSelect ? Array.from(serviceSelect.querySelectorAll('option[data-service-date]')) : [];

    var members = [];
    try {
        members = JSON.parse(membersDataEl.textContent || '[]');
    } catch (e) {
        members = [];
    }

    var noMembersFound = @json(__('pages.offerings.no_envelope_match'));
    var entryLabelTemplate = @json(__('pages.offerings.entry_label', ['number' => ':number']));
    var nextIndex = entriesContainer
        ? Array.prototype.reduce.call(entriesContainer.querySelectorAll('.offering-entry'), function (max, el) {
            return Math.max(max, parseInt(el.getAttribute('data-entry-index'), 10) || 0);
        }, -1) + 1
        : 0;

    function selectedContributionType() {
        var checked = document.querySelector('input[name="contribution_type"]:checked');
        return checked ? checked.value : 'member';
    }

    function memberLabel(member) {
        if (member.envelope) {
            return member.envelope + '-' + member.name;
        }
        return member.name;
    }

    function methodNeedsReference() {
        return ['bank_transfer', 'mobile_money', 'cheque'].indexOf(paymentSelect.value) !== -1;
    }

    function toggleOfferingTypeOther() {
        var isOther = typeSelect && typeSelect.value === 'other';
        if (otherGroup) {
            otherGroup.style.display = isOther ? '' : 'none';
        }
        if (otherInput) {
            otherInput.required = isOther;
        }
    }

    function setEnabled(field, enabled) {
        if (!field) {
            return;
        }
        field.disabled = !enabled;
    }

    function toggleReferences() {
        var show = methodNeedsReference();
        document.querySelectorAll('.entry-reference-group').forEach(function (group) {
            group.style.display = show ? '' : 'none';
            var input = group.querySelector('.entry-reference');
            if (input && !show) {
                input.value = '';
            }
            if (input) {
                input.required = show && selectedContributionType() === 'member';
            }
        });

        if (generalReferenceGroup) {
            generalReferenceGroup.style.display = show ? '' : 'none';
        }
        if (generalReference && !show) {
            generalReference.value = '';
        }
        if (generalReference) {
            generalReference.required = show && selectedContributionType() === 'general';
        }
    }

    function toggleContributionMode() {
        var type = selectedContributionType();
        var isMember = type === 'member';

        if (memberBulkGroup) {
            memberBulkGroup.style.display = isMember ? '' : 'none';
        }
        if (generalGroup) {
            generalGroup.style.display = isMember ? 'none' : '';
        }
        if (helpMember) {
            helpMember.style.display = isMember ? '' : 'none';
        }
        if (helpGeneral) {
            helpGeneral.style.display = isMember ? 'none' : '';
        }

        entriesContainer.querySelectorAll('.entry-amount').forEach(function (input) {
            input.required = isMember;
            setEnabled(input, isMember);
        });
        entriesContainer.querySelectorAll('.entry-member-id, .entry-member-search, .entry-reference').forEach(function (input) {
            setEnabled(input, isMember);
        });

        setEnabled(serviceSelect, !isMember);
        setEnabled(generalAmount, !isMember);
        setEnabled(generalReference, !isMember);
        if (generalAmount) {
            generalAmount.required = !isMember;
        }

        toggleReferences();
        if (!isMember) {
            suggestServiceForDate();
        }
    }

    function updateEntryLabels() {
        var entries = entriesContainer.querySelectorAll('.offering-entry');
        entries.forEach(function (entry, i) {
            var label = entry.querySelector('.entry-label');
            if (label) {
                label.textContent = entryLabelTemplate.replace(':number', String(i + 1));
            }
            var removeBtn = entry.querySelector('.remove-entry');
            if (removeBtn) {
                removeBtn.style.display = entries.length > 1 ? '' : 'none';
            }
        });
    }

    function hideResults(resultsEl) {
        resultsEl.style.display = 'none';
        resultsEl.innerHTML = '';
    }

    function searchMembers(term) {
        var q = (term || '').toLowerCase().trim();
        if (!q) {
            return members.slice(0, 25);
        }

        return members.filter(function (member) {
            var envelope = (member.envelope || '').toLowerCase();
            var name = (member.name || '').toLowerCase();
            var label = memberLabel(member).toLowerCase();
            return envelope.indexOf(q) !== -1 || name.indexOf(q) !== -1 || label.indexOf(q) !== -1;
        }).slice(0, 25);
    }

    function renderResults(entry, items) {
        var resultsEl = entry.querySelector('.entry-member-results');
        var memberIdInput = entry.querySelector('.entry-member-id');
        var searchInput = entry.querySelector('.entry-member-search');
        resultsEl.innerHTML = '';

        if (!items.length) {
            resultsEl.innerHTML = '<div class="list-group-item text-muted">' + noMembersFound + '</div>';
            resultsEl.style.display = 'block';
            return;
        }

        items.forEach(function (member) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'list-group-item list-group-item-action';

            if (member.envelope) {
                var strong = document.createElement('strong');
                strong.textContent = member.envelope;
                btn.appendChild(strong);
                btn.appendChild(document.createTextNode('-' + member.name));
            } else {
                btn.textContent = member.name;
            }

            btn.addEventListener('click', function () {
                memberIdInput.value = member.id;
                searchInput.value = memberLabel(member);
                hideResults(resultsEl);
            });
            resultsEl.appendChild(btn);
        });

        resultsEl.style.display = 'block';
    }

    function bindEntry(entry) {
        var searchInput = entry.querySelector('.entry-member-search');
        var memberIdInput = entry.querySelector('.entry-member-id');
        var resultsEl = entry.querySelector('.entry-member-results');
        var removeBtn = entry.querySelector('.remove-entry');

        searchInput.addEventListener('focus', function () {
            if (selectedContributionType() !== 'member') {
                return;
            }
            renderResults(entry, searchMembers(searchInput.value));
        });

        searchInput.addEventListener('input', function () {
            memberIdInput.value = '';
            renderResults(entry, searchMembers(searchInput.value));
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                hideResults(resultsEl);
            }
        });

        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                if (entriesContainer.querySelectorAll('.offering-entry').length <= 1) {
                    return;
                }
                entry.remove();
                updateEntryLabels();
            });
        }
    }

    function addEntry() {
        var html = template.innerHTML
            .replace(/__INDEX__/g, String(nextIndex))
            .replace(/__NUMBER__/g, String(entriesContainer.querySelectorAll('.offering-entry').length + 1));
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        var entry = wrapper.firstElementChild;
        entriesContainer.appendChild(entry);
        nextIndex += 1;
        bindEntry(entry);
        toggleReferences();
        updateEntryLabels();
        entry.querySelector('.entry-member-search').focus();
    }

    function suggestServiceForDate() {
        if (!serviceSelect || selectedContributionType() !== 'general' || serviceSelect.disabled) {
            return;
        }

        serviceOptions.forEach(function (option) {
            option.hidden = false;
            option.disabled = false;
        });

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

    entriesContainer.querySelectorAll('.offering-entry').forEach(bindEntry);

    if (addBtn) {
        addBtn.addEventListener('click', addEntry);
    }

    document.querySelectorAll('input[name="contribution_type"]').forEach(function (radio) {
        radio.addEventListener('change', toggleContributionMode);
    });

    if (paymentSelect) {
        paymentSelect.addEventListener('change', toggleReferences);
    }

    if (typeSelect) {
        typeSelect.addEventListener('change', toggleOfferingTypeOther);
        toggleOfferingTypeOther();
    }

    if (offeringDate) {
        offeringDate.addEventListener('change', suggestServiceForDate);
    }

    document.addEventListener('click', function (e) {
        entriesContainer.querySelectorAll('.offering-entry').forEach(function (entry) {
            var searchInput = entry.querySelector('.entry-member-search');
            var resultsEl = entry.querySelector('.entry-member-results');
            if (!resultsEl.contains(e.target) && e.target !== searchInput) {
                hideResults(resultsEl);
            }
        });
    });

    if (form) {
        form.addEventListener('submit', function (e) {
            if (selectedContributionType() !== 'member') {
                return;
            }

            var missing = false;
            entriesContainer.querySelectorAll('.offering-entry').forEach(function (entry) {
                var memberId = entry.querySelector('.entry-member-id').value;
                if (!memberId) {
                    missing = true;
                    entry.querySelector('.entry-member-search').classList.add('is-invalid');
                }
            });
            if (missing) {
                e.preventDefault();
                alert(@json(__('pages.offerings.select_member_required')));
            }
        });
    }

    toggleContributionMode();
    updateEntryLabels();
});
</script>
