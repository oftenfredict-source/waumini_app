<script>
(function () {
    var typeSelect = document.getElementById('pledge_type');
    var otherGroup = document.getElementById('pledgeTypeOtherGroup');
    var otherInput = document.getElementById('pledge_type_other');
    var frequencySelect = document.getElementById('payment_frequency');
    var oneTimeGroup = document.getElementById('oneTimeDateGroup');
    var oneTimeInput = document.getElementById('one_time_payment_date');

    function togglePledgeTypeOther() {
        var isOther = typeSelect && typeSelect.value === 'other';
        if (otherGroup) {
            otherGroup.style.display = isOther ? 'block' : 'none';
        }
        if (otherInput) {
            otherInput.required = isOther;
        }
    }

    function toggleOneTimeDate() {
        var isOneTime = frequencySelect && frequencySelect.value === 'one_time';
        if (oneTimeGroup) {
            oneTimeGroup.style.display = isOneTime ? 'block' : 'none';
        }
        if (oneTimeInput) {
            oneTimeInput.required = isOneTime;
        }
    }

    if (typeSelect) {
        typeSelect.addEventListener('change', togglePledgeTypeOther);
        togglePledgeTypeOther();
    }

    if (frequencySelect) {
        frequencySelect.addEventListener('change', toggleOneTimeDate);
        toggleOneTimeDate();
    }
})();
</script>
