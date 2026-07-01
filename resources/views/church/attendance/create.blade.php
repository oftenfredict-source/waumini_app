@extends('layouts.church')

@section('title', __('pages.attendance.record_attendance'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-check-square-o',
    'title' => __('pages.attendance.record_attendance'),
    'subtitle' => __('pages.attendance.record_subtitle', [
        'min' => config('membership.sunday_school_min_age', 3),
        'max' => config('membership.sunday_school_max_age', 12),
    ]),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.attendance'), 'route' => 'church.attendance.index'],
        ['label' => __('pages.shared.breadcrumb_record')],
    ],
])

<div class="tile mb-3">
    <h3 class="tile-title">{{ __('pages.attendance.step_select') }}</h3>
    <form method="GET" action="{{ route('church.attendance.create') }}" class="row" id="sourceSelectForm">
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('common.type') }}</label>
                <select name="source_type" id="source_type" class="form-control" required>
                    <option value="">{{ __('pages.shared.select_type') }}</option>
                    @foreach($sourceTypes as $type)
                        <option value="{{ $type->value }}" @selected($selectedSourceType === $type->value)>
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('pages.shared.service_event') }}</label>
                <select name="source_id" id="source_id" class="form-control" required>
                    <option value="">{{ __('pages.attendance.select_service_event') }}</option>
                    <optgroup label="{{ __('pages.shared.church_services') }}">
                        @foreach($memberServices as $service)
                            <option value="{{ $service->id }}" data-type="church_service" data-mode="main_service"
                                @selected($selectedSourceType === 'church_service' && $selectedSourceId == $service->id)>
                                {{ $service->displayTitle() }} — {{ $service->service_date->format('M d, Y') }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="{{ __('pages.shared.sunday_school') }}">
                        @foreach($sundaySchoolServices as $service)
                            <option value="{{ $service->id }}" data-type="church_service" data-mode="sunday_school"
                                @selected($selectedSourceType === 'church_service' && $selectedSourceId == $service->id)>
                                {{ $service->displayTitle() }} — {{ $service->service_date->format('M d, Y') }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="{{ __('menu.special_events') }}">
                        @foreach($specialEvents as $event)
                            <option value="{{ $event->id }}" data-type="special_event" data-mode="mixed"
                                @selected($selectedSourceType === 'special_event' && $selectedSourceId == $event->id)>
                                {{ $event->title }} — {{ $event->event_date->format('M d, Y') }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-secondary btn-block mb-3">{{ __('pages.shared.load') }}</button>
        </div>
    </form>
</div>

@if($selectedSource)
    <form method="POST" action="{{ route('church.attendance.store') }}">
        @csrf
        <input type="hidden" name="source_type" value="{{ $selectedSourceType }}">
        <input type="hidden" name="source_id" value="{{ $selectedSourceId }}">

        @if($attendanceMode === 'sunday_school')
            <div class="alert alert-success">
                <i class="fa fa-child"></i>
                {{ __('pages.attendance.sunday_school_alert', [
                    'min' => config('membership.sunday_school_min_age', 3),
                    'max' => config('membership.sunday_school_max_age', 12),
                ]) }}
            </div>
        @elseif($attendanceMode === 'main_service')
            <div class="alert alert-info">
                <i class="fa fa-users"></i>
                {{ __('pages.attendance.main_service_alert', [
                    'min' => config('membership.main_service_child_min_age', 13),
                    'max' => config('membership.child_independence_age', 21) - 1,
                ]) }}
            </div>
        @endif

        <div class="tile mb-3">
            <h3 class="tile-title">{{ __('pages.attendance.step_guests_notes') }}</h3>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>{{ __('pages.shared.guests_non_members') }}</label>
                        <input type="number" name="guests_count" class="form-control" min="0"
                            value="{{ old('guests_count', $guestsCount) }}">
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="form-group">
                        <label>{{ __('pages.shared.notes') }}</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes', $notes) }}"
                            placeholder="{{ __('pages.attendance.optional_notes_placeholder') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @if($attendanceMode !== 'sunday_school')
                <div class="col-md-{{ $attendanceMode === 'main_service' ? '6' : '7' }}">
                    <div class="tile">
                        <h3 class="tile-title">{{ __('pages.attendance.members_heading', ['count' => $members->count()]) }}</h3>
                        <div class="mb-2">
                            <input type="text" id="memberSearch" class="form-control form-control-sm" placeholder="{{ __('pages.attendance.search_members') }}">
                        </div>
                        <div class="attendance-list member-list" style="max-height: 420px; overflow-y: auto;">
                            @forelse($members as $member)
                                <label class="d-block attendance-item mb-2" data-name="{{ strtolower($member->full_name) }}">
                                    <input type="checkbox" name="member_ids[]" value="{{ $member->id }}"
                                        @checked(in_array($member->id, old('member_ids', $attendedMemberIds)))>
                                    {{ $member->full_name }}
                                    <small class="text-muted">({{ $member->member_number }})</small>
                                </label>
                            @empty
                                <p class="text-muted">{{ __('pages.attendance.no_active_members') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            @if($attendanceMode === 'sunday_school')
                <div class="col-md-12">
                    <div class="tile">
                        <h3 class="tile-title">{{ __('pages.attendance.sunday_school_children', ['count' => $sundaySchoolChildren->count()]) }}</h3>
                        <div class="mb-2">
                            <input type="text" id="childSearch" class="form-control form-control-sm" placeholder="{{ __('pages.attendance.search_children') }}">
                        </div>
                        <div class="attendance-list child-list" style="max-height: 420px; overflow-y: auto;">
                            @forelse($sundaySchoolChildren as $child)
                                <label class="d-block attendance-item mb-2" data-name="{{ strtolower($child->full_name) }}">
                                    <input type="checkbox" name="dependant_ids[]" value="{{ $child->id }}"
                                        @checked(in_array($child->id, old('dependant_ids', $attendedDependantIds)))>
                                    {{ $child->full_name }}
                                    @if($child->age())
                                        <small class="text-muted">{{ __('pages.shared.age_label', ['age' => $child->age()]) }}</small>
                                    @endif
                                </label>
                            @empty
                                <p class="text-muted">{{ __('pages.attendance.no_sunday_school_children', [
                                    'min' => config('membership.sunday_school_min_age', 3),
                                    'max' => config('membership.sunday_school_max_age', 12),
                                ]) }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @elseif($attendanceMode === 'main_service')
                <div class="col-md-6">
                    <div class="tile">
                        <h3 class="tile-title">{{ __('pages.attendance.teenagers_heading', ['count' => $teenagers->count()]) }}</h3>
                        <div class="mb-2">
                            <input type="text" id="teenSearch" class="form-control form-control-sm" placeholder="{{ __('pages.attendance.search_teenagers') }}">
                        </div>
                        <div class="attendance-list teen-list" style="max-height: 420px; overflow-y: auto;">
                            @forelse($teenagers as $teen)
                                <label class="d-block attendance-item mb-2" data-name="{{ strtolower($teen->full_name) }}">
                                    <input type="checkbox" name="dependant_ids[]" value="{{ $teen->id }}"
                                        @checked(in_array($teen->id, old('dependant_ids', $attendedDependantIds)))>
                                    {{ $teen->full_name }}
                                    @if($teen->age())
                                        <small class="text-muted">{{ __('pages.shared.age_label', ['age' => $teen->age()]) }}</small>
                                    @endif
                                </label>
                            @empty
                                <p class="text-muted">{{ __('pages.attendance.no_teenagers', [
                                    'min' => config('membership.main_service_child_min_age', 13),
                                    'max' => config('membership.child_independence_age', 21) - 1,
                                ]) }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @else
                <div class="col-md-5">
                    <div class="tile">
                        <h3 class="tile-title">{{ __('pages.attendance.children_heading', ['count' => $allChildren->count()]) }}</h3>
                        <div class="mb-2">
                            <input type="text" id="childSearch" class="form-control form-control-sm" placeholder="{{ __('pages.attendance.search_children') }}">
                        </div>
                        <div class="attendance-list child-list" style="max-height: 420px; overflow-y: auto;">
                            @forelse($allChildren as $child)
                                <label class="d-block attendance-item mb-2" data-name="{{ strtolower($child->full_name) }}">
                                    <input type="checkbox" name="dependant_ids[]" value="{{ $child->id }}"
                                        @checked(in_array($child->id, old('dependant_ids', $attendedDependantIds)))>
                                    {{ $child->full_name }}
                                    @if($child->age())
                                        <small class="text-muted">{{ __('pages.shared.age_label', ['age' => $child->age()]) }}</small>
                                    @endif
                                </label>
                            @empty
                                <p class="text-muted">{{ __('pages.attendance.no_children') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.attendance.save_attendance') }}</button>
            <a href="{{ route('church.attendance.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </div>
    </form>
@else
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> {{ __('pages.attendance.select_hint') }}
    </div>
@endif
@endsection

@push('scripts')
<script>
    (function () {
        var sourceType = document.getElementById('source_type');
        var sourceId = document.getElementById('source_id');

        function filterSourceOptions() {
            if (!sourceType || !sourceId) return;
            var type = sourceType.value;
            Array.prototype.forEach.call(sourceId.options, function (option) {
                if (!option.dataset.type) return;
                option.hidden = type !== '' && option.dataset.type !== type;
            });
            if (sourceId.selectedOptions[0] && sourceId.selectedOptions[0].hidden) {
                sourceId.value = '';
            }
        }

        if (sourceType) {
            sourceType.addEventListener('change', filterSourceOptions);
            filterSourceOptions();
        }

        function bindSearch(inputId, listClass) {
            var input = document.getElementById(inputId);
            if (!input) return;
            input.addEventListener('input', function () {
                var term = input.value.toLowerCase();
                document.querySelectorAll('.' + listClass + ' .attendance-item').forEach(function (item) {
                    item.style.display = item.dataset.name.indexOf(term) !== -1 ? 'block' : 'none';
                });
            });
        }

        bindSearch('memberSearch', 'member-list');
        bindSearch('childSearch', 'child-list');
        bindSearch('teenSearch', 'teen-list');
    })();
</script>
@endpush
