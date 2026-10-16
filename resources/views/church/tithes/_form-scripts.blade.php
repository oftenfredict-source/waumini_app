<script>
document.addEventListener('DOMContentLoaded', function () {
    var paymentSelect = document.getElementById('payment_method');
    var referenceGroup = document.getElementById('referenceGroup');
    if (paymentSelect && referenceGroup) {
        function toggleReference() {
            var needsRef = ['bank_transfer', 'mobile_money', 'cheque'].includes(paymentSelect.value);
            referenceGroup.style.display = needsRef ? '' : 'none';
        }

        paymentSelect.addEventListener('change', toggleReference);
        toggleReference();
    }

    var membersDataEl = document.getElementById('titheMembersData');
    var memberIdInput = document.getElementById('tithe_member_id');
    var memberSearch = document.getElementById('tithe_member_search');
    var memberResults = document.getElementById('tithe_member_results');
    if (!membersDataEl || !memberIdInput || !memberSearch || !memberResults) {
        return;
    }

    var members = [];
    try {
        members = JSON.parse(membersDataEl.textContent || '[]');
    } catch (e) {
        members = [];
    }

    var noMembersFound = @json(__('pages.offerings.no_envelope_match'));

    function memberLabel(member) {
        if (member.envelope) {
            return member.envelope + '-' + member.name;
        }
        return member.name;
    }

    function hideMemberResults() {
        memberResults.style.display = 'none';
        memberResults.innerHTML = '';
    }

    function renderMemberResults(items) {
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
                strong.textContent = member.envelope;
                btn.appendChild(strong);
                btn.appendChild(document.createTextNode('-' + member.name));
            } else {
                btn.textContent = member.name;
            }

            btn.addEventListener('click', function () {
                memberIdInput.value = member.id;
                memberSearch.value = memberLabel(member);
                hideMemberResults();
            });
            memberResults.appendChild(btn);
        });

        memberResults.style.display = 'block';
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

    memberSearch.addEventListener('focus', function () {
        renderMemberResults(searchMembers(memberSearch.value));
    });

    memberSearch.addEventListener('input', function () {
        memberIdInput.value = '';
        renderMemberResults(searchMembers(memberSearch.value));
    });

    memberSearch.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            hideMemberResults();
        }
    });

    document.addEventListener('click', function (e) {
        if (!memberResults.contains(e.target) && e.target !== memberSearch) {
            hideMemberResults();
        }
    });

    memberSearch.closest('form')?.addEventListener('submit', function (e) {
        if (!memberIdInput.value) {
            e.preventDefault();
            memberSearch.focus();
            renderMemberResults(searchMembers(memberSearch.value));
        }
    });
});
</script>
