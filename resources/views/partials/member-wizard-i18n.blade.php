@php
    $memberWizardI18n = [
        'yes' => __('members.options.yes'),
        'no' => __('members.options.no'),
        'locations' => [
            'select_region' => __('members.locations.select_region'),
            'select_region_first' => __('members.locations.select_region_first'),
            'select_district' => __('members.locations.select_district'),
            'unable_load_regions' => __('members.locations.unable_load_regions'),
        ],
        'summary' => trans('members.summary'),
    ];
@endphp
<script>
    window.memberWizardI18n = @json($memberWizardI18n);
</script>
