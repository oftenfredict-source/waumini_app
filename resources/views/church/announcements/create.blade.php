@extends('layouts.church')

@section('title', 'Create Announcement')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> Create Announcement</h1>
        <p>Publish a new church announcement</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.announcements.index') }}">Announcements</a></li>
        <li class="breadcrumb-item">Create</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.announcements.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title') }}" required>
                    @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Type *</label>
                    <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                        @foreach($types as $type)
                            <option value="{{ $type->value }}" @selected(old('type', 'general') === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    @error('type')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Content *</label>
                    <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="6" required>{{ old('content') }}</textarea>
                    @error('content')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Audience *</label>
                    <select name="target_type" id="target_type" class="form-control @error('target_type') is-invalid @enderror" required>
                        @foreach($targetTypes as $targetType)
                            <option value="{{ $targetType->value }}" @selected(old('target_type', 'all') === $targetType->value)>{{ $targetType->label() }}</option>
                        @endforeach
                    </select>
                    @error('target_type')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-8" id="members-group" style="display: none;">
                <div class="form-group">
                    <label>Select Members *</label>
                    <select name="member_ids[]" id="member_ids" class="form-control @error('member_ids') is-invalid @enderror" multiple size="6">
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" @selected(collect(old('member_ids', []))->contains($member->id))>
                                {{ $member->full_name }} ({{ $member->member_number }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple members.</small>
                    @error('member_ids')<small class="text-danger d-block">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-8" id="department-group" style="display: none;">
                <div class="form-group">
                    <label>Department *</label>
                    <select name="department_id" id="department_id" class="form-control @error('department_id') is-invalid @enderror">
                        <option value="">Select department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>
                                {{ $department->name }} ({{ $department->members_count }} members)
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                        value="{{ old('start_date', now()->toDateString()) }}">
                    @error('start_date')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                        value="{{ old('end_date') }}">
                    <small class="text-muted">Leave empty for no expiry</small>
                    @error('end_date')<small class="text-danger d-block">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group pt-4">
                    <div class="animated-checkbox">
                        <label>
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                            <span class="label-text">Publish as active</span>
                        </label>
                    </div>
                    <div class="animated-checkbox">
                        <label>
                            <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned'))>
                            <span class="label-text">Pin this announcement</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="tile-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Publish Announcement</button>
            <a href="{{ route('church.announcements.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var targetType = document.getElementById('target_type');
        var membersGroup = document.getElementById('members-group');
        var departmentGroup = document.getElementById('department-group');
        var memberIds = document.getElementById('member_ids');
        var departmentId = document.getElementById('department_id');

        function toggleAudienceFields() {
            var value = targetType ? targetType.value : 'all';
            if (membersGroup) membersGroup.style.display = value === 'specific' ? 'block' : 'none';
            if (departmentGroup) departmentGroup.style.display = value === 'department' ? 'block' : 'none';
            if (memberIds) memberIds.required = value === 'specific';
            if (departmentId) departmentId.required = value === 'department';
        }

        if (targetType) {
            targetType.addEventListener('change', toggleAudienceFields);
            toggleAudienceFields();
        }
    })();
</script>
@endpush
