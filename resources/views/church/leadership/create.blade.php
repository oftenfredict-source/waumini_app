@extends('layouts.church')

@section('title', 'Assign Leadership')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> Assign Leadership</h1>
        <p>Assign a leadership position to a church member</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.leadership.index') }}">Leadership</a></li>
        <li class="breadcrumb-item">Assign</li>
    </ul>
</div>

@if($members->isEmpty())
    <div class="alert alert-warning">
        <i class="fa fa-exclamation-triangle"></i>
        You need at least one active member before assigning leadership.
        <a href="{{ route('church.members.create') }}">Register a member</a> first.
    </div>
@endif

<div class="tile">
    <form method="POST" action="{{ route('church.leadership.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Member *</label>
                    <select name="member_id" class="form-control @error('member_id') is-invalid @enderror" required
                        {{ $members->isEmpty() ? 'disabled' : '' }}>
                        <option value="">Select member</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>
                                {{ $member->full_name }} ({{ $member->member_number }})
                            </option>
                        @endforeach
                    </select>
                    @error('member_id')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Position *</label>
                    <select name="position" id="position" class="form-control @error('position') is-invalid @enderror" required
                        {{ $members->isEmpty() ? 'disabled' : '' }}>
                        <option value="">Select position</option>
                        @foreach($positions as $value => $label)
                            <option value="{{ $value }}" @selected(old('position') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('position')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6" id="position-title-group" style="display: none;">
                <div class="form-group">
                    <label>Custom Position Title *</label>
                    <input type="text" name="position_title" id="position_title"
                        class="form-control @error('position_title') is-invalid @enderror"
                        value="{{ old('position_title') }}" placeholder="e.g. Media Team Leader">
                    @error('position_title')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Appointment Date *</label>
                    <input type="date" name="appointment_date"
                        class="form-control @error('appointment_date') is-invalid @enderror"
                        value="{{ old('appointment_date', now()->toDateString()) }}" required
                        {{ $members->isEmpty() ? 'disabled' : '' }}>
                    @error('appointment_date')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                        value="{{ old('end_date') }}" {{ $members->isEmpty() ? 'disabled' : '' }}>
                    <small class="text-muted">Optional — for term-limited assignments</small>
                    @error('end_date')<small class="text-danger d-block">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Appointed By</label>
                    <input type="text" name="appointed_by" class="form-control"
                        value="{{ old('appointed_by', auth()->user()->name) }}"
                        {{ $members->isEmpty() ? 'disabled' : '' }}>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="2"
                        {{ $members->isEmpty() ? 'disabled' : '' }}>{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control" rows="2"
                        {{ $members->isEmpty() ? 'disabled' : '' }}>{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        <div class="tile-footer">
            @if($members->isNotEmpty())
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Assign Position</button>
            @endif
            <a href="{{ route('church.leadership.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var positionSelect = document.getElementById('position');
        var titleGroup = document.getElementById('position-title-group');
        var titleInput = document.getElementById('position_title');

        function toggleTitleField() {
            var show = positionSelect && positionSelect.value === 'other';
            if (titleGroup) {
                titleGroup.style.display = show ? 'block' : 'none';
            }
            if (titleInput) {
                titleInput.required = show;
            }
        }

        if (positionSelect) {
            positionSelect.addEventListener('change', toggleTitleField);
            toggleTitleField();
        }
    })();
</script>
@endpush
