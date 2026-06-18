@php
    $guest = $guest ?? null;
    $defaultLinkType = old('event_link_type');
    if ($defaultLinkType === null) {
        if ($guest?->church_service_id) {
            $defaultLinkType = 'church_service';
        } elseif ($guest?->special_event_id) {
            $defaultLinkType = 'special_event';
        } else {
            $defaultLinkType = 'none';
        }
    }
    $defaultGuestType = old('guest_type', $guest?->guest_type?->value ?? 'promised');
@endphp

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Guest Type *</label>
            <select name="guest_type" class="form-control @error('guest_type') is-invalid @enderror" required>
                @foreach($guestTypes as $type)
                    <option value="{{ $type->value }}" @selected($defaultGuestType === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>
            @error('guest_type')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $guest?->name) }}" required>
            @error('name')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Phone Number *</label>
            <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror"
                value="{{ old('phone_number', $guest?->phone_number) }}" placeholder="+2557XXXXXXXX" required>
            @error('phone_number')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $guest?->email) }}">
            @error('email')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Visit / Promised Date *</label>
            <input type="date" name="promised_date" id="promised_date" class="form-control @error('promised_date') is-invalid @enderror"
                value="{{ old('promised_date', $guest?->promised_date?->toDateString() ?? now()->toDateString()) }}" required>
            @error('promised_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    @if($guest)
        <div class="col-md-4">
            <div class="form-group">
                <label>Status *</label>
                <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $guest->status->value) === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                @error('status')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    @endif

    <div class="col-md-12">
        <div class="form-group">
            <label>Link to Event or Service</label>
            <select name="event_link_type" id="event_link_type" class="form-control @error('event_link_type') is-invalid @enderror">
                <option value="none" @selected($defaultLinkType === 'none')>— Date only (no specific service/event) —</option>
                <option value="church_service" @selected($defaultLinkType === 'church_service')>Church Service</option>
                <option value="special_event" @selected($defaultLinkType === 'special_event')>Special Event</option>
            </select>
            @error('event_link_type')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-6" id="churchServiceGroup" @if($defaultLinkType !== 'church_service') style="display:none;" @endif>
        <div class="form-group">
            <label>Church Service</label>
            <select name="church_service_id" id="church_service_id" class="form-control @error('church_service_id') is-invalid @enderror">
                <option value="">— Select service —</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}"
                        data-service-date="{{ $service->service_date?->toDateString() }}"
                        @selected(old('church_service_id', $guest?->church_service_id) == $service->id)>
                        {{ $service->offeringSelectionLabel() }}
                    </option>
                @endforeach
            </select>
            @error('church_service_id')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-6" id="specialEventGroup" @if($defaultLinkType !== 'special_event') style="display:none;" @endif>
        <div class="form-group">
            <label>Special Event</label>
            <select name="special_event_id" id="special_event_id" class="form-control @error('special_event_id') is-invalid @enderror">
                <option value="">— Select event —</option>
                @foreach($events as $event)
                    <option value="{{ $event->id }}"
                        data-event-date="{{ $event->event_date?->toDateString() }}"
                        @selected(old('special_event_id', $guest?->special_event_id) == $event->id)>
                        {{ $event->title }} — {{ $event->event_date?->format('M d, Y') }}
                    </option>
                @endforeach
            </select>
            @error('special_event_id')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $guest?->notes) }}</textarea>
            @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>

    @unless($guest)
        <div class="col-md-12">
            <div class="form-check">
                <label class="form-check-label">
                    <input type="checkbox" name="send_sms" value="1" class="form-check-input" @checked(old('send_sms'))>
                    Send welcome / reminder SMS now after saving
                </label>
            </div>
        </div>
    @endunless
</div>

@push('scripts')
<script>
(function () {
    var linkType = document.getElementById('event_link_type');
    var serviceGroup = document.getElementById('churchServiceGroup');
    var eventGroup = document.getElementById('specialEventGroup');
    var serviceSelect = document.getElementById('church_service_id');
    var eventSelect = document.getElementById('special_event_id');
    var promisedDate = document.getElementById('promised_date');

    function toggleLinkGroups() {
        var type = linkType ? linkType.value : 'none';
        if (serviceGroup) serviceGroup.style.display = type === 'church_service' ? '' : 'none';
        if (eventGroup) eventGroup.style.display = type === 'special_event' ? '' : 'none';
        if (serviceSelect) serviceSelect.required = type === 'church_service';
        if (eventSelect) eventSelect.required = type === 'special_event';
    }

    function syncDateFromSelection() {
        if (!promisedDate) return;
        if (linkType?.value === 'church_service' && serviceSelect?.selectedOptions[0]) {
            var d = serviceSelect.selectedOptions[0].getAttribute('data-service-date');
            if (d) promisedDate.value = d;
        }
        if (linkType?.value === 'special_event' && eventSelect?.selectedOptions[0]) {
            var ed = eventSelect.selectedOptions[0].getAttribute('data-event-date');
            if (ed) promisedDate.value = ed;
        }
    }

    if (linkType) linkType.addEventListener('change', toggleLinkGroups);
    if (serviceSelect) serviceSelect.addEventListener('change', syncDateFromSelection);
    if (eventSelect) eventSelect.addEventListener('change', syncDateFromSelection);
    toggleLinkGroups();
})();
</script>
@endpush
