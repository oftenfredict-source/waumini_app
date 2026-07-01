@php
    $event = $event ?? null;
    $isCreate = ! $event;
@endphp
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.bereavements.form_deceased_name') }} *</label>
            <input type="text" name="deceased_name" class="form-control @error('deceased_name') is-invalid @enderror"
                value="{{ old('deceased_name', $event?->deceased_name) }}" required>
            @error('deceased_name')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.bereavements.form_related_member') }}</label>
            <select name="affected_member_id" id="affected_member_id" class="form-control @error('affected_member_id') is-invalid @enderror">
                <option value="">{{ __('pages.bereavements.form_not_linked_member') }}</option>
                @foreach($members as $member)
                    <option value="{{ $member->id }}" data-name="{{ $member->full_name }}"
                        @selected(old('affected_member_id', $event?->affected_member_id) == $member->id)>
                        {{ $member->full_name }}@if($member->envelope_number) ({{ $member->envelope_number }})@endif
                    </option>
                @endforeach
            </select>
            @error('affected_member_id')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('pages.shared.incident_date') }} *</label>
            <input type="date" name="incident_date" class="form-control @error('incident_date') is-invalid @enderror"
                value="{{ old('incident_date', $event?->incident_date?->toDateString() ?? now()->toDateString()) }}" required>
            @error('incident_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('pages.shared.contribution_start') }} *</label>
            <input type="date" name="contribution_start_date" class="form-control @error('contribution_start_date') is-invalid @enderror"
                value="{{ old('contribution_start_date', $event?->contribution_start_date?->toDateString() ?? now()->toDateString()) }}" required>
            @error('contribution_start_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('pages.shared.contribution_end') }} *</label>
            <input type="date" name="contribution_end_date" class="form-control @error('contribution_end_date') is-invalid @enderror"
                value="{{ old('contribution_end_date', $event?->contribution_end_date?->toDateString() ?? now()->addDays(14)->toDateString()) }}" required>
            @error('contribution_end_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.shared.family_details') }}</label>
            <textarea name="family_details" rows="3" class="form-control @error('family_details') is-invalid @enderror"
                placeholder="{{ __('pages.bereavements.form_family_placeholder') }}">{{ old('family_details', $event?->family_details) }}</textarea>
            @error('family_details')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.shared.related_departments') }}</label>
            <input type="text" name="related_departments" class="form-control @error('related_departments') is-invalid @enderror"
                value="{{ old('related_departments', $event?->related_departments) }}"
                placeholder="{{ __('pages.bereavements.form_departments_placeholder') }}">
            @error('related_departments')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label>{{ __('pages.shared.notes') }}</label>
            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $event?->notes) }}</textarea>
            @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    @if($isCreate)
        <div class="col-md-12">
            <div class="form-group">
                <label>{{ __('pages.bereavements.form_members_track') }}</label>
                <p class="text-muted small mb-2">{{ __('pages.bereavements.form_members_track_hint') }}</p>
                <div class="border rounded p-3" style="max-height: 220px; overflow-y: auto;">
                    @foreach($members as $member)
                        <div class="custom-control custom-checkbox mb-1">
                            <input type="checkbox" class="custom-control-input" name="member_ids[]"
                                id="member_{{ $member->id }}" value="{{ $member->id }}"
                                @checked(in_array($member->id, old('member_ids', [])))>
                            <label class="custom-control-label" for="member_{{ $member->id }}">
                                {{ $member->full_name }}@if($member->envelope_number) ({{ $member->envelope_number }})@endif
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('member_ids')<small class="text-danger d-block">{{ $message }}</small>@enderror
                @error('member_ids.*')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
        </div>
    @endif
    @if($event && $event->status->value === 'closed')
        <div class="col-md-12">
            <div class="form-group">
                <label>{{ __('pages.shared.fund_usage') }}</label>
                <textarea name="fund_usage" rows="3" class="form-control @error('fund_usage') is-invalid @enderror"
                    placeholder="{{ __('pages.bereavements.form_fund_usage_placeholder') }}">{{ old('fund_usage', $event?->fund_usage) }}</textarea>
                @error('fund_usage')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    @endif
</div>
