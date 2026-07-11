<script>
(function () {
    var peopleSearchUrl = @json(route('church.services.people-search'));
    var noMembersFound = @json(__('pages.services.no_members_found'));
    var searchingLabel = @json(__('pages.services.searching'));

    var preacherType = document.getElementById('preacher_type');
    var coordinatorType = document.getElementById('coordinator_type');
    var preacherMemberId = document.getElementById('preacher_member_id');
    var pastorSelect = document.getElementById('preacher_member_id_pastor');
    var leaderSelect = document.getElementById('preacher_member_id_leader');

    function show(id, visible) {
        var el = document.getElementById(id);
        if (el) el.style.display = visible ? '' : 'none';
    }

    function syncPreacherMemberId() {
        if (!preacherType || !preacherMemberId) return;
        var type = preacherType.value;
        if (type === 'pastor' && pastorSelect) {
            preacherMemberId.value = pastorSelect.value || '';
        } else if (type === 'leader' && leaderSelect) {
            preacherMemberId.value = leaderSelect.value || '';
        } else if (type !== 'member') {
            if (type !== 'guest') preacherMemberId.value = '';
        }
    }

    function togglePreacherFields() {
        var type = preacherType ? preacherType.value : '';
        show('preacherPastorWrap', type === 'pastor');
        show('preacherLeaderWrap', type === 'leader');
        show('preacherMemberWrap', type === 'member');
        show('preacherGuestNameWrap', type === 'guest');
        show('preacherGuestPhoneWrap', type === 'guest');

        if (type !== 'member') {
            var search = document.getElementById('preacher_member_search');
            var results = document.getElementById('preacher_member_results');
            if (type !== 'pastor' && type !== 'leader' && preacherMemberId) {
                if (type !== 'guest') preacherMemberId.value = '';
            }
            if (type !== 'member' && search && !['pastor', 'leader'].includes(type)) {
                // keep label only for member type
            }
            if (results) results.style.display = 'none';
        }

        if (type === 'guest' && preacherMemberId) {
            preacherMemberId.value = '';
        }

        syncPreacherMemberId();
    }

    function toggleCoordinatorFields() {
        var type = coordinatorType ? coordinatorType.value : '';
        show('coordinatorMemberWrap', type === 'member');
        show('coordinatorGuestNameWrap', type === 'guest');
        show('coordinatorGuestPhoneWrap', type === 'guest');

        if (type !== 'member') {
            var results = document.getElementById('coordinator_member_results');
            if (results) results.style.display = 'none';
            if (type !== 'guest') {
                var idInput = document.getElementById('coordinator_member_id');
                if (idInput) idInput.value = '';
            }
        }

        if (type === 'guest') {
            var idInput = document.getElementById('coordinator_member_id');
            if (idInput) idInput.value = '';
        }
    }

    function bindMemberSearch(searchInputId, resultsId, hiddenId) {
        var input = document.getElementById(searchInputId);
        var results = document.getElementById(resultsId);
        var hidden = document.getElementById(hiddenId);
        if (!input || !results || !hidden) return;

        var timer = null;

        function renderItems(items) {
            results.innerHTML = '';
            if (!items.length) {
                results.innerHTML = '<div class="list-group-item text-muted">' + noMembersFound + '</div>';
                results.style.display = 'block';
                return;
            }

            items.forEach(function (item) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action';
                btn.textContent = item.name + (item.member_number ? ' (' + item.member_number + ')' : '');
                btn.addEventListener('click', function () {
                    hidden.value = item.id;
                    input.value = item.name + (item.member_number ? ' (' + item.member_number + ')' : '');
                    results.style.display = 'none';
                    results.innerHTML = '';
                });
                results.appendChild(btn);
            });
            results.style.display = 'block';
        }

        input.addEventListener('input', function () {
            hidden.value = '';
            var q = input.value.trim();
            clearTimeout(timer);
            if (q.length < 2) {
                results.style.display = 'none';
                results.innerHTML = '';
                return;
            }

            results.innerHTML = '<div class="list-group-item text-muted">' + searchingLabel + '</div>';
            results.style.display = 'block';

            timer = setTimeout(function () {
                fetch(peopleSearchUrl + '?type=member&q=' + encodeURIComponent(q), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) { renderItems(data.data || []); })
                    .catch(function () {
                        results.innerHTML = '<div class="list-group-item text-danger">' + noMembersFound + '</div>';
                    });
            }, 250);
        });

        document.addEventListener('click', function (e) {
            if (!results.contains(e.target) && e.target !== input) {
                results.style.display = 'none';
            }
        });
    }

    if (preacherType) {
        preacherType.addEventListener('change', togglePreacherFields);
        togglePreacherFields();
    }
    if (coordinatorType) {
        coordinatorType.addEventListener('change', toggleCoordinatorFields);
        toggleCoordinatorFields();
    }
    if (pastorSelect) pastorSelect.addEventListener('change', syncPreacherMemberId);
    if (leaderSelect) leaderSelect.addEventListener('change', syncPreacherMemberId);

    bindMemberSearch('preacher_member_search', 'preacher_member_results', 'preacher_member_id');
    bindMemberSearch('coordinator_member_search', 'coordinator_member_results', 'coordinator_member_id');

    var form = document.getElementById('createServiceForm') || document.getElementById('editServiceForm');
    if (form) {
        form.addEventListener('submit', function () {
            syncPreacherMemberId();
        });
    }
})();
</script>
