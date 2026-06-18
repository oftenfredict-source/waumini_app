@extends('layouts.church')

@section('title', 'New Request')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> Submit a Request</h1>
        <p>Choose the type of request and the leader who should handle it</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.member.requests.index') }}">My Requests</a></li>
        <li class="breadcrumb-item">New</li>
    </ul>
</div>

<div class="tile">
    @if($leaders->isEmpty())
        <div class="alert alert-warning">
            No leaders are currently available to receive requests. Please contact your church office.
        </div>
    @else
        <form method="POST" action="{{ route('church.member.requests.store') }}" id="memberRequestForm">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Request Type <span class="text-danger">*</span></label>
                        <select name="type" id="request_type" class="form-control @error('type') is-invalid @enderror" required>
                            <option value="">Select type</option>
                            @foreach($types as $type)
                                <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Assign To (Responsible Leader) <span class="text-danger">*</span></label>
                        <select name="assigned_leader_id" class="form-control @error('assigned_leader_id') is-invalid @enderror" required>
                            <option value="">Select leader</option>
                            @foreach($leaders as $leader)
                                <option value="{{ $leader->id }}" @selected((string) old('assigned_leader_id') === (string) $leader->id)>
                                    {{ $leader->positionLabel() }} — {{ $leader->member?->full_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_leader_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">For baptism requests, assign to the Pastor when possible.</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Subject <span class="text-danger">*</span></label>
                <input type="text" name="subject" id="request_subject" class="form-control @error('subject') is-invalid @enderror"
                       value="{{ old('subject') }}" maxlength="200" required placeholder="Brief summary of your request">
                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div id="baptismRequestFields" style="display:none;">
                <div class="alert alert-info py-2">
                    <i class="fa fa-tint"></i> Select who should be baptized. If your children need baptism, choose them below.
                </div>
                <div class="form-group">
                    <label>Who is requesting baptism? <span class="text-danger">*</span></label>
                    <select name="baptism_scope" id="baptism_scope" class="form-control @error('baptism_scope') is-invalid @enderror">
                        <option value="">Select</option>
                        <option value="self" @selected(old('baptism_scope') === 'self')>Myself only</option>
                        <option value="children" @selected(old('baptism_scope') === 'children')>My child(ren) only</option>
                        <option value="both" @selected(old('baptism_scope') === 'both')>Myself and my child(ren)</option>
                    </select>
                    @error('baptism_scope')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" id="baptismChildrenWrap" style="display:none;">
                    <label>Select child(ren) to be baptized <span class="text-danger">*</span></label>
                    @if($children->isEmpty())
                        <p class="text-muted mb-0">No children are registered on your family profile. Contact the church office to register your children first.</p>
                    @else
                        @foreach($children as $child)
                            <div class="animated-checkbox">
                                <label>
                                    <input type="checkbox" name="child_dependant_ids[]" value="{{ $child->id }}"
                                           @checked(in_array($child->id, old('child_dependant_ids', [])))>
                                    <span class="label-text">
                                        {{ $child->full_name }}
                                        @if($child->date_of_birth)
                                            <small class="text-muted">(DOB: {{ $child->date_of_birth->format('M d, Y') }})</small>
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
                    <label>Preferred Baptism Date</label>
                    <input type="date" name="preferred_baptism_date" class="form-control @error('preferred_baptism_date') is-invalid @enderror"
                           value="{{ old('preferred_baptism_date') }}">
                    @error('preferred_baptism_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group" id="requestDetailsWrap">
                <label>Details <span class="text-danger" id="detailsRequiredMark">*</span></label>
                <textarea name="description" id="request_description" rows="6" class="form-control @error('description') is-invalid @enderror"
                          placeholder="Provide all details needed to process your request (dates, destination, issue description, etc.)">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted" id="baptismDetailsHint" style="display:none;">Optional notes for the baptism request (e.g. special arrangements).</small>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-paper-plane"></i> Submit Request
            </button>
            <a href="{{ route('church.member.requests.index') }}" class="btn btn-secondary">Cancel</a>
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

    function toggleBaptismRequest() {
        var isBaptism = typeSelect && typeSelect.value === 'baptism_request';
        if (baptismFields) baptismFields.style.display = isBaptism ? 'block' : 'none';
        if (description) description.required = !isBaptism;
        if (detailsRequired) detailsRequired.style.display = isBaptism ? 'none' : 'inline';
        if (baptismHint) baptismHint.style.display = isBaptism ? 'block' : 'none';
        if (isBaptism && subject && !subject.value) {
            subject.value = 'Baptism Request';
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
