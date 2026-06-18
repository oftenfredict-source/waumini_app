@extends('layouts.church')

@section('title', 'Record Attendance')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-check-square-o"></i> Record Attendance</h1>
        <p>Members for church services · Children for Sunday School (ages {{ config('membership.sunday_school_min_age', 3) }}–{{ config('membership.sunday_school_max_age', 12) }})</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.attendance.index') }}">Attendance</a></li>
        <li class="breadcrumb-item">Record</li>
    </ul>
</div>

<div class="tile mb-3">
    <h3 class="tile-title">1. Select Service or Event</h3>
    <form method="GET" action="{{ route('church.attendance.create') }}" class="row" id="sourceSelectForm">
        <div class="col-md-4">
            <div class="form-group">
                <label>Type</label>
                <select name="source_type" id="source_type" class="form-control" required>
                    <option value="">Select type</option>
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
                <label>Service / Event</label>
                <select name="source_id" id="source_id" class="form-control" required>
                    <option value="">Select service or event</option>
                    <optgroup label="Church Services">
                        @foreach($memberServices as $service)
                            <option value="{{ $service->id }}" data-type="church_service" data-mode="main_service"
                                @selected($selectedSourceType === 'church_service' && $selectedSourceId == $service->id)>
                                {{ $service->displayTitle() }} — {{ $service->service_date->format('M d, Y') }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Sunday School">
                        @foreach($sundaySchoolServices as $service)
                            <option value="{{ $service->id }}" data-type="church_service" data-mode="sunday_school"
                                @selected($selectedSourceType === 'church_service' && $selectedSourceId == $service->id)>
                                {{ $service->displayTitle() }} — {{ $service->service_date->format('M d, Y') }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Special Events">
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
            <button type="submit" class="btn btn-secondary btn-block mb-3">Load</button>
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
                <strong>Sunday School attendance</strong> — mark children aged
                {{ config('membership.sunday_school_min_age', 3) }}–{{ config('membership.sunday_school_max_age', 12) }} only.
            </div>
        @elseif($attendanceMode === 'main_service')
            <div class="alert alert-info">
                <i class="fa fa-users"></i>
                <strong>Main service attendance</strong> — mark adult members and teenagers aged
                {{ config('membership.main_service_child_min_age', 13) }}–{{ config('membership.child_independence_age', 21) - 1 }}.
            </div>
        @endif

        <div class="tile mb-3">
            <h3 class="tile-title">2. Guests & Notes</h3>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Guests (non-members)</label>
                        <input type="number" name="guests_count" class="form-control" min="0"
                            value="{{ old('guests_count', $guestsCount) }}">
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="form-group">
                        <label>Notes</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes', $notes) }}"
                            placeholder="Optional attendance notes">
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @if($attendanceMode !== 'sunday_school')
                <div class="col-md-{{ $attendanceMode === 'main_service' ? '6' : '7' }}">
                    <div class="tile">
                        <h3 class="tile-title">Members ({{ $members->count() }})</h3>
                        <div class="mb-2">
                            <input type="text" id="memberSearch" class="form-control form-control-sm" placeholder="Search members...">
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
                                <p class="text-muted">No active members found.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            @if($attendanceMode === 'sunday_school')
                <div class="col-md-12">
                    <div class="tile">
                        <h3 class="tile-title">Sunday School Children ({{ $sundaySchoolChildren->count() }})</h3>
                        <div class="mb-2">
                            <input type="text" id="childSearch" class="form-control form-control-sm" placeholder="Search children...">
                        </div>
                        <div class="attendance-list child-list" style="max-height: 420px; overflow-y: auto;">
                            @forelse($sundaySchoolChildren as $child)
                                <label class="d-block attendance-item mb-2" data-name="{{ strtolower($child->full_name) }}">
                                    <input type="checkbox" name="dependant_ids[]" value="{{ $child->id }}"
                                        @checked(in_array($child->id, old('dependant_ids', $attendedDependantIds)))>
                                    {{ $child->full_name }}
                                    @if($child->age())
                                        <small class="text-muted">(age {{ $child->age() }})</small>
                                    @endif
                                </label>
                            @empty
                                <p class="text-muted">No Sunday School children (ages {{ config('membership.sunday_school_min_age', 3) }}–{{ config('membership.sunday_school_max_age', 12) }}) registered with date of birth.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @elseif($attendanceMode === 'main_service')
                <div class="col-md-6">
                    <div class="tile">
                        <h3 class="tile-title">Teenagers ({{ $teenagers->count() }})</h3>
                        <div class="mb-2">
                            <input type="text" id="teenSearch" class="form-control form-control-sm" placeholder="Search teenagers...">
                        </div>
                        <div class="attendance-list teen-list" style="max-height: 420px; overflow-y: auto;">
                            @forelse($teenagers as $teen)
                                <label class="d-block attendance-item mb-2" data-name="{{ strtolower($teen->full_name) }}">
                                    <input type="checkbox" name="dependant_ids[]" value="{{ $teen->id }}"
                                        @checked(in_array($teen->id, old('dependant_ids', $attendedDependantIds)))>
                                    {{ $teen->full_name }}
                                    @if($teen->age())
                                        <small class="text-muted">(age {{ $teen->age() }})</small>
                                    @endif
                                </label>
                            @empty
                                <p class="text-muted">No teenagers (ages {{ config('membership.main_service_child_min_age', 13) }}–{{ config('membership.child_independence_age', 21) - 1 }}) registered.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @else
                <div class="col-md-5">
                    <div class="tile">
                        <h3 class="tile-title">Children ({{ $allChildren->count() }})</h3>
                        <div class="mb-2">
                            <input type="text" id="childSearch" class="form-control form-control-sm" placeholder="Search children...">
                        </div>
                        <div class="attendance-list child-list" style="max-height: 420px; overflow-y: auto;">
                            @forelse($allChildren as $child)
                                <label class="d-block attendance-item mb-2" data-name="{{ strtolower($child->full_name) }}">
                                    <input type="checkbox" name="dependant_ids[]" value="{{ $child->id }}"
                                        @checked(in_array($child->id, old('dependant_ids', $attendedDependantIds)))>
                                    {{ $child->full_name }}
                                    @if($child->age())
                                        <small class="text-muted">(age {{ $child->age() }})</small>
                                    @endif
                                </label>
                            @empty
                                <p class="text-muted">No children registered.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="tile-footer mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Attendance</button>
            <a href="{{ route('church.attendance.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@else
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> Select a church service, <strong>Sunday School</strong>, or special event above, then click <strong>Load</strong>.
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
