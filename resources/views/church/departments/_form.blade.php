@php
    $departmentModel = $department ?? null;
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.shared.department_name') }} *</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $departmentModel?->name) }}" placeholder="{{ __('pages.departments.name_placeholder') }}" required>
            @error('name')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('common.status') }} *</label>
            <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $departmentModel?->status?->value ?? 'active') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            @error('status')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.departments.department_leader') }}</label>
            <select name="head_id" class="form-control @error('head_id') is-invalid @enderror">
                <option value="">{{ __('pages.shared.select_member_optional') }}</option>
                @foreach($members as $member)
                    <option value="{{ $member->id }}" @selected(old('head_id', $departmentModel?->head_id) == $member->id)>
                        {{ $member->full_name }} ({{ $member->member_number }})
                    </option>
                @endforeach
            </select>
            @error('head_id')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label>{{ __('common.description') }}</label>
            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                rows="3" placeholder="{{ __('pages.departments.description_placeholder') }}">{{ old('description', $departmentModel?->description) }}</textarea>
            @error('description')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
</div>
