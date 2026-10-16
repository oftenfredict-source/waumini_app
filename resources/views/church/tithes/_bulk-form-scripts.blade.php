<script>
document.addEventListener('DOMContentLoaded', function () {
    var paymentSelect = document.getElementById('payment_method');
    var entriesContainer = document.getElementById('titheEntries');
    var addBtn = document.getElementById('addTitheEntry');
    var template = document.getElementById('titheEntryTemplate');
    var membersDataEl = document.getElementById('titheMembersData');
    var form = entriesContainer ? entriesContainer.closest('form') : null;

    var members = [];
    try {
        members = JSON.parse(membersDataEl.textContent || '[]');
    } catch (e) {
        members = [];
    }

    var noMembersFound = @json(__('pages.offerings.no_envelope_match'));
    var entryLabelTemplate = @json(__('pages.tithes.entry_label', ['number' => ':number']));
    var nextIndex = entriesContainer
        ? Array.prototype.reduce.call(entriesContainer.querySelectorAll('.tithe-entry'), function (max, el) {
            return Math.max(max, parseInt(el.getAttribute('data-entry-index'), 10) || 0);
        }, -1) + 1
        : 0;

    function memberLabel(member) {
        if (member.envelope) {
            return member.envelope + '-' + member.name;
        }
        return member.name;
    }

    function methodNeedsReference() {
        return ['bank_transfer', 'mobile_money', 'cheque'].indexOf(paymentSelect.value) !== -1;
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
                input.required = show;
            }
        });
    }

    function updateEntryLabels() {
        var entries = entriesContainer.querySelectorAll('.tithe-entry');
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
                if (entriesContainer.querySelectorAll('.tithe-entry').length <= 1) {
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
            .replace(/__NUMBER__/g, String(entriesContainer.querySelectorAll('.tithe-entry').length + 1));
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

    entriesContainer.querySelectorAll('.tithe-entry').forEach(bindEntry);

    if (addBtn) {
        addBtn.addEventListener('click', addEntry);
    }

    if (paymentSelect) {
        paymentSelect.addEventListener('change', toggleReferences);
        toggleReferences();
    }

    document.addEventListener('click', function (e) {
        entriesContainer.querySelectorAll('.tithe-entry').forEach(function (entry) {
            var searchInput = entry.querySelector('.entry-member-search');
            var resultsEl = entry.querySelector('.entry-member-results');
            if (!resultsEl.contains(e.target) && e.target !== searchInput) {
                hideResults(resultsEl);
            }
        });
    });

    if (form) {
        form.addEventListener('submit', function (e) {
            var missing = false;
            entriesContainer.querySelectorAll('.tithe-entry').forEach(function (entry) {
                var memberId = entry.querySelector('.entry-member-id').value;
                if (!memberId) {
                    missing = true;
                    entry.querySelector('.entry-member-search').classList.add('is-invalid');
                }
            });
            if (missing) {
                e.preventDefault();
                alert(@json(__('pages.tithes.select_member_required')));
            }
        });
    }

    updateEntryLabels();
});
</script>
