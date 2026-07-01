@extends('layouts.church')

@section('title', __('pages.services.create_service'))

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-plus',
    'title' => __('pages.services.create_service'),
    'subtitle' => __('pages.services.create_subtitle'),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.services'), 'route' => 'church.services.index'],
        ['label' => __('pages.shared.breadcrumb_create')],
    ],
])

<div class="tile">
    <form method="POST" action="{{ route('church.services.store') }}" id="createServiceForm">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('pages.services.service_type') }} *</label>
                    <select name="service_type" id="service_type" class="form-control @error('service_type') is-invalid @enderror" required>
                        <option value="">{{ __('pages.shared.select_type') }}</option>
                        @foreach($serviceTypes as $type)
                            <option value="{{ $type->value }}" @selected(old('service_type') === $type->value)>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_type')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6" id="extraTitleGroup" style="display: none;">
                <div class="form-group">
                    <label>{{ __('pages.services.extra_service_title') }} *</label>
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title') }}" placeholder="{{ __('pages.services.extra_title_placeholder') }}">
                    @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>{{ __('pages.services.service_date') }} *</label>
                    <input type="date" name="service_date" class="form-control @error('service_date') is-invalid @enderror"
                        value="{{ old('service_date', now()->toDateString()) }}" required>
                    @error('service_date')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>{{ __('pages.shared.start_time') }}</label>
                    <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror"
                        value="{{ old('start_time') }}">
                    @error('start_time')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>{{ __('pages.shared.end_time') }}</label>
                    <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror"
                        value="{{ old('end_time') }}">
                    @error('end_time')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('pages.shared.theme') }}</label>
                    <input type="text" name="theme" class="form-control @error('theme') is-invalid @enderror"
                        value="{{ old('theme') }}" placeholder="{{ __('pages.services.theme_placeholder') }}">
                    @error('theme')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('pages.services.preacher_speaker') }}</label>
                    <input type="text" name="preacher" class="form-control @error('preacher') is-invalid @enderror"
                        value="{{ old('preacher') }}" placeholder="{{ __('pages.services.preacher_placeholder') }}">
                    @error('preacher')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('common.venue') }}</label>
                    <input type="text" name="venue" class="form-control @error('venue') is-invalid @enderror"
                        value="{{ old('venue') }}" placeholder="{{ __('pages.services.venue_placeholder') }}">
                    @error('venue')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('common.status') }} *</label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', 'scheduled') === $status->value)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{ __('pages.shared.notes') }}</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                        placeholder="{{ __('pages.services.notes_placeholder') }}">{{ old('notes') }}</textarea>
                    @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>
        <div class="tile-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.shared.save_item', ['item' => __('pages.services.item')]) }}</button>
            <a href="{{ route('church.services.index') }}" class="btn btn-secondary">{{ __('common.cancel') }}</a>
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
