@php
    $event = $event ?? null;
@endphp
<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            <label>{{ __('pages.special_events.form_title') }} *</label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                value="{{ old('title', $event?->title) }}" placeholder="{{ __('pages.special_events.form_title_placeholder') }}" required>
            @error('title')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('common.category') }} *</label>
            <select name="category" id="category" class="form-control @error('category') is-invalid @enderror" required>
                <option value="">{{ __('pages.shared.select_category') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->value }}" @selected(old('category', $event?->category?->value) === $category->value)>
                        {{ $category->label() }}
                    </option>
                @endforeach
            </select>
            @error('category')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4" id="categoryOtherGroup" style="display: none;">
        <div class="form-group">
            <label>{{ __('pages.special_events.form_other_category') }} *</label>
            <input type="text" name="category_other" id="category_other"
                class="form-control @error('category_other') is-invalid @enderror"
                value="{{ old('category_other', $event?->category_other) }}" placeholder="{{ __('pages.special_events.form_specify_category') }}">
            @error('category_other')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('pages.special_events.form_event_date') }} *</label>
            <input type="date" name="event_date" class="form-control @error('event_date') is-invalid @enderror"
                value="{{ old('event_date', $event?->event_date?->toDateString() ?? now()->toDateString()) }}" required>
            @error('event_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('pages.shared.start_time') }}</label>
            <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror"
                value="{{ old('start_time', $event?->start_time ? \Illuminate\Support\Str::of($event->start_time)->substr(0, 5) : '') }}">
            @error('start_time')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('pages.shared.end_time') }}</label>
            <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror"
                value="{{ old('end_time', $event?->end_time ? \Illuminate\Support\Str::of($event->end_time)->substr(0, 5) : '') }}">
            @error('end_time')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.shared.speaker_guest') }}</label>
            <input type="text" name="speaker" class="form-control @error('speaker') is-invalid @enderror"
                value="{{ old('speaker', $event?->speaker) }}" placeholder="{{ __('pages.special_events.form_speaker_placeholder') }}">
            @error('speaker')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('common.venue') }}</label>
            <input type="text" name="venue" class="form-control @error('venue') is-invalid @enderror"
                value="{{ old('venue', $event?->venue) }}" placeholder="{{ __('pages.special_events.form_venue_placeholder') }}">
            @error('venue')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('pages.special_events.form_budget') }}</label>
            <input type="number" name="budget_amount" class="form-control @error('budget_amount') is-invalid @enderror"
                value="{{ old('budget_amount', $event?->budget_amount) }}" min="0" step="0.01" placeholder="0.00">
            @error('budget_amount')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('pages.shared.expected_attendance') }}</label>
            <input type="number" name="expected_attendance" class="form-control @error('expected_attendance') is-invalid @enderror"
                value="{{ old('expected_attendance', $event?->expected_attendance) }}" min="0" placeholder="{{ __('pages.special_events.form_expected_placeholder') }}">
            @error('expected_attendance')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('common.status') }} *</label>
            <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $event?->status?->value ?? 'scheduled') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            @error('status')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label>{{ __('common.description') }}</label>
            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3"
                placeholder="{{ __('pages.special_events.form_description_placeholder') }}">{{ old('description', $event?->description) }}</textarea>
            @error('description')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label>{{ __('pages.shared.notes') }}</label>
            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2"
                placeholder="{{ __('pages.special_events.form_notes_placeholder') }}">{{ old('notes', $event?->notes) }}</textarea>
            @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
</div>
