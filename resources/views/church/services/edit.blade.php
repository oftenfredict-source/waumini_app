@extends('layouts.church')

@section('title', 'Edit Service')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-pencil"></i> Edit Service</h1>
        <p>Update {{ $service->displayTitle() }} — {{ $service->service_date->format('M d, Y') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.services.index') }}">Services</a></li>
        <li class="breadcrumb-item">Edit</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.services.update', $service) }}" id="editServiceForm">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Service Type *</label>
                    <select name="service_type" id="service_type" class="form-control @error('service_type') is-invalid @enderror" required>
                        <option value="">Select type</option>
                        @foreach($serviceTypes as $type)
                            <option value="{{ $type->value }}" @selected(old('service_type', $service->service_type->value) === $type->value)>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_type')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6" id="extraTitleGroup" style="display: none;">
                <div class="form-group">
                    <label>Extra Service Title *</label>
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $service->title) }}" placeholder="e.g. Youth Revival, Harvest Thanksgiving">
                    @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Service Date *</label>
                    <input type="date" name="service_date" class="form-control @error('service_date') is-invalid @enderror"
                        value="{{ old('service_date', $service->service_date->toDateString()) }}" required>
                    @error('service_date')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Start Time</label>
                    <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror"
                        value="{{ old('start_time', $service->start_time ? \Illuminate\Support\Str::of($service->start_time)->substr(0, 5) : '') }}">
                    @error('start_time')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>End Time</label>
                    <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror"
                        value="{{ old('end_time', $service->end_time ? \Illuminate\Support\Str::of($service->end_time)->substr(0, 5) : '') }}">
                    @error('end_time')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Theme</label>
                    <input type="text" name="theme" class="form-control @error('theme') is-invalid @enderror"
                        value="{{ old('theme', $service->theme) }}" placeholder="e.g. Walking in Faith">
                    @error('theme')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Preacher / Speaker</label>
                    <input type="text" name="preacher" class="form-control @error('preacher') is-invalid @enderror"
                        value="{{ old('preacher', $service->preacher) }}" placeholder="Name of preacher or speaker">
                    @error('preacher')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Venue</label>
                    <input type="text" name="venue" class="form-control @error('venue') is-invalid @enderror"
                        value="{{ old('venue', $service->venue) }}" placeholder="e.g. Main Sanctuary">
                    @error('venue')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $service->status->value) === $status->value)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                        placeholder="Additional service details">{{ old('notes', $service->notes) }}</textarea>
                    @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>
        <div class="tile-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Service</button>
            <a href="{{ route('church.services.show', $service) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var typeSelect = document.getElementById('service_type');
        var titleGroup = document.getElementById('extraTitleGroup');
        var titleInput = document.getElementById('title');

        function toggleExtraTitle() {
            var isExtra = typeSelect && typeSelect.value === 'extra';
            if (titleGroup) {
                titleGroup.style.display = isExtra ? 'block' : 'none';
            }
            if (titleInput) {
                titleInput.required = isExtra;
            }
        }

        if (typeSelect) {
            typeSelect.addEventListener('change', toggleExtraTitle);
            toggleExtraTitle();
        }
    })();
</script>
@endpush
