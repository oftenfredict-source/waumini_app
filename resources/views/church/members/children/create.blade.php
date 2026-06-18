@extends('layouts.church')

@section('title', 'Add Child')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> Add Child</h1>
        <p>Register a child under a church member parent or a non-member guardian</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.members.index') }}">Members</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.members.children.index') }}">Children</a></li>
        <li class="breadcrumb-item">Add Child</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.members.children.store') }}" id="addChildForm">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Is the parent a church member? *</label>
                    <select name="parent_type" id="parent_type" class="form-control @error('parent_type') is-invalid @enderror" required>
                        <option value="member" @selected(old('parent_type', 'member') === 'member')>Yes — select church member</option>
                        <option value="non_member" @selected(old('parent_type') === 'non_member')>No — enter guardian details</option>
                    </select>
                    @error('parent_type')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>

        <div id="memberParentSection" class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Parent Church Member *</label>
                    <select name="member_id" id="member_id" class="form-control @error('member_id') is-invalid @enderror">
                        <option value="">Select member</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" @selected($selectedMemberId == $member->id)>
                                {{ $member->full_name }} ({{ $member->member_number }})
                            </option>
                        @endforeach
                    </select>
                    @if($members->isEmpty())
                        <small class="text-warning d-block mt-1">
                            No active members yet. Use "No — enter guardian details" or
                            <a href="{{ route('church.members.create') }}">register a member</a> first.
                        </small>
                    @endif
                    @error('member_id')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>

        <div id="guardianSection" class="row" style="display: none;">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Guardian Full Name *</label>
                    <input type="text" name="guardian_full_name" id="guardian_full_name"
                        class="form-control @error('guardian_full_name') is-invalid @enderror"
                        value="{{ old('guardian_full_name') }}">
                    @error('guardian_full_name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Guardian Phone</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">+255</span></div>
                        <input type="text" name="guardian_phone" id="guardian_phone"
                            class="form-control @error('guardian_phone') is-invalid @enderror"
                            value="{{ old('guardian_phone') }}" placeholder="7XXXXXXXX">
                    </div>
                    @error('guardian_phone')<small class="text-danger d-block">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Relationship to Child</label>
                    <select name="guardian_relationship" id="guardian_relationship" class="form-control">
                        <option value="">Select relationship</option>
                        @foreach(['Father', 'Mother', 'Guardian', 'Uncle', 'Aunt', 'Grandparent', 'Other'] as $rel)
                            <option value="{{ $rel }}" @selected(old('guardian_relationship') === $rel)>{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Child Full Name *</label>
                    <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                        value="{{ old('full_name') }}" required>
                    @error('full_name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                        <option value="">Select gender</option>
                        <option value="male" @selected(old('gender') === 'male')>Male</option>
                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                    </select>
                    @error('gender')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="date_of_birth"
                        class="form-control @error('date_of_birth') is-invalid @enderror"
                        value="{{ old('date_of_birth') }}" max="{{ now()->subDay()->toDateString() }}">
                    <small class="text-muted">Tracks age for conversion at {{ config('membership.child_independence_age', 21) }}+</small>
                    @error('date_of_birth')<small class="text-danger d-block">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Additional Note</label>
                    <input type="text" name="relationship_note"
                        class="form-control @error('relationship_note') is-invalid @enderror"
                        value="{{ old('relationship_note') }}" placeholder="e.g. adopted, stepchild">
                    @error('relationship_note')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>

        <div class="tile-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Add Child</button>
            <a href="{{ route('church.members.children.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var parentType = document.getElementById('parent_type');
        var memberSection = document.getElementById('memberParentSection');
        var guardianSection = document.getElementById('guardianSection');
        var memberId = document.getElementById('member_id');
        var guardianName = document.getElementById('guardian_full_name');

        function toggleParentFields() {
            var isMember = parentType && parentType.value === 'member';
            if (memberSection) memberSection.style.display = isMember ? 'flex' : 'none';
            if (guardianSection) guardianSection.style.display = isMember ? 'none' : 'flex';
            if (memberId) memberId.required = isMember;
            if (guardianName) guardianName.required = !isMember;
        }

        if (parentType) {
            parentType.addEventListener('change', toggleParentFields);
            toggleParentFields();
        }
    })();
</script>
@endpush
