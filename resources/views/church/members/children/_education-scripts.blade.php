<script>
(function () {
    var studentCheckbox = document.getElementById('is_student');
    var educationWrap = document.getElementById('childEducationFields');
    var regionEl = document.getElementById('school_region');
    var districtEl = document.getElementById('school_district');
    var locationsUrl = @json(asset('data/tanzania-locations.json'));
    var selectRegionLabel = @json(__('members.locations.select_region'));
    var selectDistrictLabel = @json(__('members.locations.select_district'));
    var selectRegionFirstLabel = @json(__('members.locations.select_region_first'));
    var unableLoadLabel = @json(__('members.locations.unable_load_regions'));
    var regions = [];

    function toggleEducationFields() {
        if (!educationWrap || !studentCheckbox) return;
        educationWrap.style.display = studentCheckbox.checked ? 'flex' : 'none';
        if (!studentCheckbox.checked) {
            var level = document.getElementById('education_level');
            var schoolName = document.querySelector('[name="school_name"]');
            var ward = document.querySelector('[name="school_ward"]');
            var street = document.querySelector('[name="school_street"]');
            if (level) level.value = '';
            if (schoolName) schoolName.value = '';
            if (ward) ward.value = '';
            if (street) street.value = '';
            if (regionEl) regionEl.value = '';
            if (districtEl) {
                districtEl.innerHTML = '<option value="">' + selectRegionFirstLabel + '</option>';
                districtEl.disabled = true;
            }
        }
    }

    function populateRegions(selected) {
        if (!regionEl) return;
        regionEl.innerHTML = '<option value="">' + selectRegionLabel + '</option>';
        regions.forEach(function (region) {
            var option = document.createElement('option');
            option.value = region.name;
            option.textContent = region.name;
            if (region.name === selected) option.selected = true;
            regionEl.appendChild(option);
        });
    }

    function populateDistricts(regionName, selectedDistrict) {
        if (!districtEl) return;
        var region = regions.find(function (item) { return item.name === regionName; });
        var list = region ? region.districts : [];
        districtEl.innerHTML = '<option value="">' + (regionName ? selectDistrictLabel : selectRegionFirstLabel) + '</option>';
        list.forEach(function (district) {
            var option = document.createElement('option');
            option.value = district.name;
            option.textContent = district.name;
            if (district.name === selectedDistrict) option.selected = true;
            districtEl.appendChild(option);
        });
        districtEl.disabled = !regionName;
    }

    if (studentCheckbox) {
        studentCheckbox.addEventListener('change', toggleEducationFields);
        toggleEducationFields();
    }

    if (regionEl && districtEl) {
        var savedRegion = regionEl.getAttribute('data-selected') || '';
        var savedDistrict = districtEl.getAttribute('data-selected') || '';

        fetch(locationsUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                regions = data.regions || [];
                populateRegions(savedRegion);
                if (savedRegion) populateDistricts(savedRegion, savedDistrict);
            })
            .catch(function () {
                regionEl.innerHTML = '<option value="">' + unableLoadLabel + '</option>';
            });

        regionEl.addEventListener('change', function () {
            populateDistricts(this.value, '');
        });
    }
})();
</script>
