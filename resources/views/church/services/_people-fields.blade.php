@php
    $service = $service ?? null;
    $preacherTypes = \App\Enums\ServicePreacherType::cases();
    $coordinatorTypes = \App\Enums\ServiceCoordinatorType::cases();
    $oldPreacherType = old('preacher_type', $service?->preacher_type?->value);
    $oldCoordinatorType = old('coordinator_type', $service?->coordinator_type?->value);
    $oldPreacherMemberId = old('preacher_member_id', $service?->preacher_member_id);
    $oldCoordinatorMemberId = old('coordinator_member_id', $service?->coordinator_member_id);
    $preacherMemberLabel = old('preacher_member_label');
    $coordinatorMemberLabel = old('coordinator_member_label');
    if (! $preacherMemberLabel && $service?->preacherMember) {
        $preacherMemberLabel = $service->preacherMember->full_name.' ('.$service->preacherMember->member_number.')';
    }
    if (! $coordinatorMemberLabel && $service?->coordinatorMember) {
        $coordinatorMemberLabel = $service->coordinatorMember->full_name.' ('.$service->coordinatorMember->member_number.')';
    }
    $pastors = $pastors ?? collect();
    $leaders = $leaders ?? collect();
@endphp

<div class="col-md-6">
    <div class="form-group">
        <label>{{ __('pages.services.preacher_type') }}</label>
        <select name="preacher_type" id="preacher_type" class="form-control @error('preacher_type') is-invalid @enderror">
            <option value="">{{ __('members.options.select') }}</option>
            @foreach($preacherTypes as $type)
                <option value="{{ $type->value }}" @selected($oldPreacherType === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('preacher_type')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>

<div class="col-md-6" id="preacherPastorWrap" style="display:none;">
    <div class="form-group">
        <label>{{ __('pages.services.preacher_select_pastor') }}</label>
        <select name="preacher_member_id_pastor" id="preacher_member_id_pastor" class="form-control preacher-member-select">
            <option value="">{{ __('pages.services.preacher_select_pastor') }}</option>
            @foreach($pastors as $person)
                <option value="{{ $person['id'] }}"
                    @selected((string) $oldPreacherMemberId === (string) $person['id'] && $oldPreacherType === 'pastor')>
                    {{ $person['name'] }}@if(!empty($person['position'])) — {{ $person['position'] }}@endif
                </option>
            @endforeach
        </select>
        @if($pastors->isEmpty())
            <small class="text-muted">{{ __('pages.services.no_pastors_found') }}</small>
        @endif
    </div>
</div>

<div class="col-md-6" id="preacherLeaderWrap" style="display:none;">
    <div class="form-group">
        <label>{{ __('pages.services.preacher_select_leader') }}</label>
        <select name="preacher_member_id_leader" id="preacher_member_id_leader" class="form-control preacher-member-select">
            <option value="">{{ __('pages.services.preacher_select_leader') }}</option>
            @foreach($leaders as $person)
                <option value="{{ $person['id'] }}"
                    @selected((string) $oldPreacherMemberId === (string) $person['id'] && $oldPreacherType === 'leader')>
                    {{ $person['name'] }}@if(!empty($person['position'])) — {{ $person['position'] }}@endif
                </option>
            @endforeach
        </select>
        @if($leaders->isEmpty())
            <small class="text-muted">{{ __('pages.services.no_leaders_found') }}</small>
        @endif
    </div>
</div>

<div class="col-md-6" id="preacherMemberWrap" style="display:none;">
    <div class="form-group">
        <label>{{ __('pages.services.preacher_search_member') }}</label>
        <input type="text" id="preacher_member_search" class="form-control"
            value="{{ $preacherMemberLabel }}"
            placeholder="{{ __('pages.services.preacher_search_member') }}"
            autocomplete="off">
        <input type="hidden" name="preacher_member_id" id="preacher_member_id" value="{{ $oldPreacherMemberId }}">
        <div id="preacher_member_results" class="list-group mt-1" style="display:none; max-height: 220px; overflow:auto;"></div>
        @error('preacher_member_id')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>

<div class="col-md-6" id="preacherGuestNameWrap" style="display:none;">
    <div class="form-group">
        <label>{{ __('pages.services.preacher_guest_name') }}</label>
        <input type="text" name="preacher_guest_name" id="preacher_guest_name" class="form-control @error('preacher_guest_name') is-invalid @enderror"
            value="{{ old('preacher_guest_name', $oldPreacherType === 'guest' ? $service?->preacher : '') }}">
        @error('preacher_guest_name')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>
<div class="col-md-6" id="preacherGuestPhoneWrap" style="display:none;">
    <div class="form-group">
        <label>{{ __('pages.services.preacher_guest_phone') }}</label>
        <input type="text" name="preacher_guest_phone" id="preacher_guest_phone" class="form-control @error('preacher_guest_phone') is-invalid @enderror"
            value="{{ old('preacher_guest_phone', $oldPreacherType === 'guest' ? $service?->preacher_phone : '') }}">
        @error('preacher_guest_phone')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <label>{{ __('pages.services.coordinator_type') }}</label>
        <select name="coordinator_type" id="coordinator_type" class="form-control @error('coordinator_type') is-invalid @enderror">
            <option value="">{{ __('members.options.select') }}</option>
            @foreach($coordinatorTypes as $type)
                <option value="{{ $type->value }}" @selected($oldCoordinatorType === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('coordinator_type')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>

<div class="col-md-6" id="coordinatorMemberWrap" style="display:none;">
    <div class="form-group">
        <label>{{ __('pages.services.coordinator_search_member') }}</label>
        <input type="text" id="coordinator_member_search" class="form-control"
            value="{{ $coordinatorMemberLabel }}"
            placeholder="{{ __('pages.services.coordinator_search_member') }}"
            autocomplete="off">
        <input type="hidden" name="coordinator_member_id" id="coordinator_member_id" value="{{ $oldCoordinatorMemberId }}">
        <div id="coordinator_member_results" class="list-group mt-1" style="display:none; max-height: 220px; overflow:auto;"></div>
        @error('coordinator_member_id')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>

<div class="col-md-6" id="coordinatorGuestNameWrap" style="display:none;">
    <div class="form-group">
        <label>{{ __('pages.services.coordinator_guest_name') }}</label>
        <input type="text" name="coordinator_guest_name" id="coordinator_guest_name" class="form-control @error('coordinator_guest_name') is-invalid @enderror"
            value="{{ old('coordinator_guest_name', $oldCoordinatorType === 'guest' ? $service?->coordinator_name : '') }}">
        @error('coordinator_guest_name')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>
<div class="col-md-6" id="coordinatorGuestPhoneWrap" style="display:none;">
    <div class="form-group">
        <label>{{ __('pages.services.coordinator_guest_phone') }}</label>
        <input type="text" name="coordinator_guest_phone" id="coordinator_guest_phone" class="form-control @error('coordinator_guest_phone') is-invalid @enderror"
            value="{{ old('coordinator_guest_phone', $oldCoordinatorType === 'guest' ? $service?->coordinator_phone : '') }}">
        @error('coordinator_guest_phone')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>
