@extends('layouts.church')

@section('title', __('pages.leadership.assign_leadership'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-plus',
    'title' => __('pages.leadership.assign_leadership'),
    'subtitle' => __('pages.leadership.create_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.leadership'), 'route' => 'church.leadership.index'],
        ['label' => __('pages.leadership.breadcrumb_assign')],
    ],
])

@if($members->isEmpty())
    <div class="alert alert-warning">
        <i class="fa fa-exclamation-triangle"></i>
        {{ __('pages.leadership.no_members_warning') }}
        <a href="{{ route('church.members.create') }}">{{ __('pages.leadership.register_member_link') }}</a> {{ __('pages.leadership.register_member_first') }}
    </div>
@endif

<div class="tile">
    <form method="POST" action="{{ route('church.leadership.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('common.member') }} *</label>
                    <select name="member_id" class="form-control @error('member_id') is-invalid @enderror" required
                        {{ $members->isEmpty() ? 'disabled' : '' }}>
                        <option value="">{{ __('pages.shared.select_member') }}</option>
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
                    <label>{{ __('pages.shared.position') }} *</label>
                    <select name="position" id="position" class="form-control @error('position') is-invalid @enderror" required
                        {{ $members->isEmpty() ? 'disabled' : '' }}>
                        <option value="">{{ __('pages.shared.select_position') }}</option>
                        @foreach($positions as $value => $label)
                            <option value="{{ $value }}" @selected(old('position') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('position')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6" id="position-title-group" style="display: none;">
                <div class="form-group">
                    <label>{{ __('pages.leadership.custom_position_title') }} *</label>
                    <input type="text" name="position_title" id="position_title"
                        class="form-control @error('position_title') is-invalid @enderror"
                        value="{{ old('position_title') }}" placeholder="{{ __('pages.leadership.custom_position_placeholder') }}">
                    @error('position_title')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('pages.shared.appointment_date') }} *</label>
                    <input type="date" name="appointment_date"
                        class="form-control @error('appointment_date') is-invalid @enderror"
                        value="{{ old('appointment_date', now()->toDateString()) }}" required
                        {{ $members->isEmpty() ? 'disabled' : '' }}>
                    @error('appointment_date')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('pages.shared.end_date') }}</label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                        value="{{ old('end_date') }}" {{ $members->isEmpty() ? 'disabled' : '' }}>
                    <small class="text-muted">{{ __('pages.shared.end_date_optional_hint') }}</small>
                    @error('end_date')<small class="text-danger d-block">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('pages.shared.appointed_by') }}</label>
                    <input type="text" name="appointed_by" class="form-control"
                        value="{{ old('appointed_by', auth()->user()->name) }}"
                        {{ $members->isEmpty() ? 'disabled' : '' }}>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{ __('common.description') }}</label>
                    <textarea name="description" class="form-control" rows="2"
                        {{ $members->isEmpty() ? 'disabled' : '' }}>{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{ __('pages.shared.notes') }}</label>
                    <textarea name="notes" class="form-control" rows="2"
                        {{ $members->isEmpty() ? 'disabled' : '' }}>{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        <div class="tile-footer">
            @if($members->isNotEmpty())
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.leadership.assign_position_button') }}</button>
            @endif
            <a href="{{ route('church.leadership.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
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
