@extends('layouts.church')

@section('title', __('pages.member_portal_requests.create_title'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-plus',
    'title' => __('pages.member_portal_requests.create_title'),
    'subtitle' => __('pages.member_portal_requests.create_subtitle'),
    'breadcrumb' => [
        ['label' => __('pages.member_portal_requests.title'), 'route' => 'church.member.requests.index'],
        ['label' => __('pages.shared.breadcrumb_new')],
    ],
])

<div class="tile">
    @if($leaders->isEmpty())
        <div class="alert alert-warning">
            {{ __('pages.member_portal_requests.no_leaders_alert') }}
        </div>
    @else
        <form method="POST" action="{{ route('church.member.requests.store') }}" id="memberRequestForm">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ __('pages.member_portal_requests.request_type') }} <span class="text-danger">*</span></label>
                        <select name="type" id="request_type" class="form-control @error('type') is-invalid @enderror" required>
                            <option value="">{{ __('pages.shared.select_type') }}</option>
                            @foreach($types as $type)
                                <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ __('pages.member_portal_requests.assign_to_leader') }} <span class="text-danger">*</span></label>
                        <select name="assigned_leader_id" class="form-control @error('assigned_leader_id') is-invalid @enderror" required>
                            <option value="">{{ __('pages.member_portal_requests.select_leader') }}</option>
                            @foreach($leaders as $leader)
                                <option value="{{ $leader->id }}" @selected((string) old('assigned_leader_id') === (string) $leader->id)>
                                    {{ $leader->positionLabel() }} — {{ $leader->member?->full_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_leader_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">{{ __('pages.member_portal_requests.baptism_assign_hint') }}</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>{{ __('common.subject') }} <span class="text-danger">*</span></label>
                <input type="text" name="subject" id="request_subject" class="form-control @error('subject') is-invalid @enderror"
                       value="{{ old('subject') }}" maxlength="200" required placeholder="{{ __('pages.member_portal_requests.subject_placeholder') }}">
                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div id="baptismRequestFields" style="display:none;">
                <div class="alert alert-info py-2">
                    <i class="fa fa-tint"></i> {{ __('pages.member_portal_requests.baptism_info_alert') }}
                </div>
                <div class="form-group">
                    <label>{{ __('pages.member_portal_requests.baptism_scope') }} <span class="text-danger">*</span></label>
                    <select name="baptism_scope" id="baptism_scope" class="form-control @error('baptism_scope') is-invalid @enderror">
                        <option value="">{{ __('pages.shared.select') }}</option>
                        <option value="self" @selected(old('baptism_scope') === 'self')>{{ __('pages.member_portal_requests.baptism_scope_self') }}</option>
                        <option value="children" @selected(old('baptism_scope') === 'children')>{{ __('pages.member_portal_requests.baptism_scope_children') }}</option>
                        <option value="both" @selected(old('baptism_scope') === 'both')>{{ __('pages.member_portal_requests.baptism_scope_both') }}</option>
                    </select>
                    @error('baptism_scope')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" id="baptismChildrenWrap" style="display:none;">
                    <label>{{ __('pages.member_portal_requests.select_children_baptism') }} <span class="text-danger">*</span></label>
                    @if($children->isEmpty())
                        <p class="text-muted mb-0">{{ __('pages.member_portal_requests.no_children_profile') }}</p>
                    @else
                        @foreach($children as $child)
                            <div class="animated-checkbox">
                                <label>
                                    <input type="checkbox" name="child_dependant_ids[]" value="{{ $child->id }}"
                                           @checked(in_array($child->id, old('child_dependant_ids', [])))>
                                    <span class="label-text">
                                        {{ $child->full_name }}
                                        @if($child->date_of_birth)
                                            <small class="text-muted">({{ __('pages.members.dob_col') }}: {{ $child->date_of_birth->format('M d, Y') }})</small>
                                        @endif
                                    </span>
                                </label>
                            </div>
                        @endforeach
                        @error('child_dependant_ids')<small class="text-danger d-block">{{ $message }}</small>@enderror
                        @error('child_dependant_ids.*')<small class="text-danger d-block">{{ $message }}</small>@enderror
                    @endif
                </div>

                <div class="form-group">
                    <label>{{ __('pages.member_portal_requests.preferred_baptism_date') }}</label>
                    <input type="date" name="preferred_baptism_date" class="form-control @error('preferred_baptism_date') is-invalid @enderror"
                           value="{{ old('preferred_baptism_date') }}">
                    @error('preferred_baptism_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group" id="requestDetailsWrap">
                <label>{{ __('common.details') }} <span class="text-danger" id="detailsRequiredMark">*</span></label>
                <textarea name="description" id="request_description" rows="6" class="form-control @error('description') is-invalid @enderror"
                          placeholder="{{ __('pages.member_portal_requests.details_placeholder') }}">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted" id="baptismDetailsHint" style="display:none;">{{ __('pages.member_portal_requests.details_optional_hint') }}</small>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-paper-plane"></i> {{ __('pages.member_portal_requests.submit_request') }}
            </button>
            <a href="{{ route('church.member.requests.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    var typeSelect = document.getElementById('request_type');
    var baptismFields = document.getElementById('baptismRequestFields');
    var baptismScope = document.getElementById('baptism_scope');
    var childrenWrap = document.getElementById('baptismChildrenWrap');
    var description = document.getElementById('request_description');
    var detailsRequired = document.getElementById('detailsRequiredMark');
    var baptismHint = document.getElementById('baptismDetailsHint');
    var subject = document.getElementById('request_subject');
    var baptismSubjectDefault = @json(__('pages.member_portal_requests.baptism_subject_default'));

    function toggleBaptismRequest() {
        var isBaptism = typeSelect && typeSelect.value === 'baptism_request';
        if (baptismFields) baptismFields.style.display = isBaptism ? 'block' : 'none';
        if (description) description.required = !isBaptism;
        if (detailsRequired) detailsRequired.style.display = isBaptism ? 'none' : 'inline';
        if (baptismHint) baptismHint.style.display = isBaptism ? 'block' : 'none';
        if (isBaptism && subject && !subject.value) {
            subject.value = baptismSubjectDefault;
        }
        toggleChildrenSelection();
    }

    function toggleChildrenSelection() {
        var scope = baptismScope ? baptismScope.value : '';
        var showChildren = scope === 'children' || scope === 'both';
        if (childrenWrap) childrenWrap.style.display = showChildren ? 'block' : 'none';
        if (baptismScope) baptismScope.required = typeSelect && typeSelect.value === 'baptism_request';
    }

    if (typeSelect) typeSelect.addEventListener('change', toggleBaptismRequest);
    if (baptismScope) baptismScope.addEventListener('change', toggleChildrenSelection);
    toggleBaptismRequest();
})();
</script>
@endpush
