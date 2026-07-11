@extends('layouts.church')

@section('title', __('pages.members_children.edit_child'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-edit',
    'title' => __('pages.members_children.edit_child'),
    'subtitle' => __('pages.members_children.edit_subtitle', ['name' => $dependant->full_name]),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.members'), 'route' => 'church.members.index'],
        ['label' => __('pages.members_children.title'), 'route' => 'church.members.children.index'],
        ['label' => __('common.edit')],
    ],
])

<div class="tile mb-3">
    <h3 class="tile-title">{{ __('pages.members_children.parent_guardian') }}</h3>
    <p class="mb-0">
        @if($dependant->member)
            <a href="{{ route('church.members.show', $dependant->member) }}">{{ $dependant->member->full_name }}</a>
            <span class="text-muted">({{ __('pages.members_children.parent_member') }})</span>
        @elseif($dependant->guardian_full_name)
            {{ $dependant->guardian_full_name }}
            @if($dependant->guardian_relationship)
                <span class="text-muted">({{ $dependant->guardian_relationship }})</span>
            @endif
        @else
            —
        @endif
    </p>
    <p class="text-muted small mb-0 mt-2">
        {{ __('pages.members.relationship_col') }}: {{ $dependant->relationship->label() }}
    </p>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.members.children.update', $dependant) }}">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('pages.members_children.child_full_name') }}</label>
                    <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                        value="{{ old('full_name', $dependant->full_name) }}" required>
                    @error('full_name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>{{ __('members.fields.gender') }} *</label>
                    <select name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                        <option value="male" @selected(old('gender', $dependant->gender) === 'male')>{{ __('pages.shared.male') }}</option>
                        <option value="female" @selected(old('gender', $dependant->gender) === 'female')>{{ __('pages.shared.female') }}</option>
                    </select>
                    @error('gender')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>{{ __('members.fields.date_of_birth') }}</label>
                    <input type="date" name="date_of_birth"
                        class="form-control @error('date_of_birth') is-invalid @enderror"
                        value="{{ old('date_of_birth', $dependant->date_of_birth?->toDateString()) }}"
                        max="{{ now()->subDay()->toDateString() }}">
                    <small class="text-muted">{{ __('pages.members_children.dob_edit_hint') }}</small>
                    @error('date_of_birth')<small class="text-danger d-block">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('pages.members_children.additional_note') }}</label>
                    <input type="text" name="relationship_note"
                        class="form-control @error('relationship_note') is-invalid @enderror"
                        value="{{ old('relationship_note', $dependant->relationship_note) }}"
                        placeholder="{{ __('pages.members_children.note_placeholder') }}">
                    @error('relationship_note')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>

        <hr>
        <h5>{{ __('pages.members.baptism') }}</h5>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="form-check">
                        <input type="hidden" name="is_baptized" value="0">
                        <input type="checkbox" name="is_baptized" id="is_baptized" value="1" class="form-check-input"
                            @checked(old('is_baptized', $dependant->is_baptized))>
                        <label class="form-check-label" for="is_baptized">{{ __('members.fields.is_baptized') }}</label>
                    </div>
                </div>
            </div>
            <div class="col-md-4 baptism-fields">
                <div class="form-group">
                    <label>{{ __('members.fields.baptism_date') }}</label>
                    <input type="date" name="baptism_date" class="form-control @error('baptism_date') is-invalid @enderror"
                        value="{{ old('baptism_date', $dependant->baptism_date?->toDateString()) }}">
                    @error('baptism_date')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4 baptism-fields">
                <div class="form-group">
                    <label>{{ __('members.fields.baptism_place') }}</label>
                    <input type="text" name="baptism_place" class="form-control @error('baptism_place') is-invalid @enderror"
                        value="{{ old('baptism_place', $dependant->baptism_place) }}">
                    @error('baptism_place')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4 baptism-fields">
                <div class="form-group">
                    <label>{{ __('members.fields.baptized_by') }}</label>
                    <input type="text" name="baptized_by" class="form-control @error('baptized_by') is-invalid @enderror"
                        value="{{ old('baptized_by', $dependant->baptized_by) }}">
                    @error('baptized_by')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>

        @php
            $kipaimaraEnabled = (bool) app(\App\Services\Church\ChurchSettingsService::class)->get(
                auth()->user()->church,
                'kipaimara_registration_enabled',
                false
            ) || (bool) old('is_kipaimara', $dependant->is_kipaimara);
        @endphp

        @if($kipaimaraEnabled)
        <hr>
        <h5>{{ __('pages.members.kipaimara') }}</h5>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="form-check">
                        <input type="hidden" name="is_kipaimara" value="0">
                        <input type="checkbox" name="is_kipaimara" id="is_kipaimara" value="1" class="form-check-input"
                            @checked(old('is_kipaimara', $dependant->is_kipaimara))>
                        <label class="form-check-label" for="is_kipaimara">{{ __('members.fields.is_kipaimara') }}</label>
                    </div>
                </div>
            </div>
            <div class="col-md-4 kipaimara-fields">
                <div class="form-group">
                    <label>{{ __('members.fields.kipaimara_date') }}</label>
                    <input type="date" name="kipaimara_date" class="form-control @error('kipaimara_date') is-invalid @enderror"
                        value="{{ old('kipaimara_date', $dependant->kipaimara_date?->toDateString()) }}">
                    @error('kipaimara_date')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4 kipaimara-fields">
                <div class="form-group">
                    <label>{{ __('members.fields.kipaimara_place') }}</label>
                    <input type="text" name="kipaimara_place" class="form-control @error('kipaimara_place') is-invalid @enderror"
                        value="{{ old('kipaimara_place', $dependant->kipaimara_place) }}">
                    @error('kipaimara_place')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4 kipaimara-fields">
                <div class="form-group">
                    <label>{{ __('members.fields.kipaimara_by') }}</label>
                    <input type="text" name="kipaimara_by" class="form-control @error('kipaimara_by') is-invalid @enderror"
                        value="{{ old('kipaimara_by', $dependant->kipaimara_by) }}"
                        placeholder="{{ __('members.fields.kipaimara_by_placeholder') }}">
                    @error('kipaimara_by')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>
        @endif

        @php
            $childrenEducationEnabled = (bool) app(\App\Services\Church\ChurchSettingsService::class)->get(
                auth()->user()->church,
                'children_education_details_enabled',
                false
            ) || (bool) old('is_student', $dependant->is_student);
        @endphp
        @if($childrenEducationEnabled)
            @include('church.members.children._education-fields', ['dependant' => $dependant])
        @endif

        <div class="tile-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('members.save_changes') }}</button>
            <a href="{{ route('church.members.children.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
            @if($dependant->member)
                <a href="{{ route('church.members.show', $dependant->member) }}" class="btn btn-outline-info">
                    <i class="fa fa-user"></i> {{ __('pages.members_children.view_parent') }}
                </a>
            @endif
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var baptized = document.getElementById('is_baptized');
        var fields = document.querySelectorAll('.baptism-fields');

        function toggleBaptismFields() {
            var show = baptized && baptized.checked;
            fields.forEach(function (el) {
                el.style.display = show ? 'block' : 'none';
            });
        }

        if (baptized) {
            baptized.addEventListener('change', toggleBaptismFields);
            toggleBaptismFields();
        }

        var kipaimara = document.getElementById('is_kipaimara');
        var kipaimaraFields = document.querySelectorAll('.kipaimara-fields');

        function toggleKipaimaraFields() {
            var show = kipaimara && kipaimara.checked;
            kipaimaraFields.forEach(function (el) {
                el.style.display = show ? 'block' : 'none';
            });
        }

        if (kipaimara) {
            kipaimara.addEventListener('change', toggleKipaimaraFields);
            toggleKipaimaraFields();
        }
    })();
</script>
@include('church.members.children._education-scripts')
@endpush
