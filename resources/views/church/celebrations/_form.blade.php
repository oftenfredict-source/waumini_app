@php
    $celebration = $celebration ?? null;
    $isAuto = $celebration?->source === \App\Enums\CelebrationSource::Auto;
    $defaultType = old('celebration_type', $celebration?->celebration_type?->value ?? 'other');
@endphp

<div class="row">
    @unless($isAuto)
        <div class="col-md-4">
            <div class="form-group">
                <label>Celebration Type *</label>
                <select name="celebration_type" id="celebration_type" class="form-control @error('celebration_type') is-invalid @enderror" required>
                    @foreach($types as $type)
                        <option value="{{ $type->value }}" @selected($defaultType === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
                @error('celebration_type')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Link to Member (optional)</label>
                <select name="member_id" id="member_id" class="form-control @error('member_id') is-invalid @enderror">
                    <option value="">— Not linked —</option>
                    @foreach($members as $member)
                        <option value="{{ $member->id }}"
                            data-dob="{{ $member->date_of_birth?->toDateString() }}"
                            data-wedding="{{ $member->wedding_date?->toDateString() }}"
                            @selected(old('member_id', $celebration?->member_id) == $member->id)>
                            {{ $member->full_name }} ({{ $member->member_number }})
                        </option>
                    @endforeach
                </select>
                @error('member_id')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" id="celebration_title" class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title', $celebration?->title) }}" required>
                @error('title')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Celebration Date *</label>
                <input type="date" name="celebration_date" id="celebration_date" class="form-control @error('celebration_date') is-invalid @enderror"
                    value="{{ old('celebration_date', $celebration?->celebration_date?->toDateString() ?? now()->toDateString()) }}" required>
                @error('celebration_date')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Original Date</label>
                <input type="date" name="original_date" id="original_date" class="form-control @error('original_date') is-invalid @enderror"
                    value="{{ old('original_date', $celebration?->original_date?->toDateString()) }}">
                <small class="text-muted">Birth date or wedding date for reference.</small>
                @error('original_date')<small class="text-danger d-block">{{ $message }}</small>@enderror
            </div>
        </div>
        <div class="col-md-4" id="weddingTypeGroup" style="display:none;">
            <div class="form-group">
                <label>Wedding Type</label>
                <select name="wedding_type" class="form-control @error('wedding_type') is-invalid @enderror">
                    <option value="">— Select —</option>
                    @foreach($weddingTypes as $wType)
                        <option value="{{ $wType->value }}" @selected(old('wedding_type', $celebration?->wedding_type?->value) === $wType->value)>
                            {{ $wType->label() }}
                        </option>
                    @endforeach
                </select>
                @error('wedding_type')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    @else
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                This celebration was detected automatically from a member profile.
                You can update its status and notes below.
            </div>
        </div>
    @endunless

    @if($celebration)
        <div class="col-md-4">
            <div class="form-group">
                <label>Status *</label>
                <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $celebration->status->value) === $status->value)>
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
            <label>Notes</label>
            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $celebration?->notes) }}</textarea>
            @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var typeSelect = document.getElementById('celebration_type');
    var weddingGroup = document.getElementById('weddingTypeGroup');
    var memberSelect = document.getElementById('member_id');
    var titleInput = document.getElementById('celebration_title');
    var celebrationDate = document.getElementById('celebration_date');
    var originalDate = document.getElementById('original_date');

    function toggleWeddingType() {
        if (!typeSelect || !weddingGroup) return;
        weddingGroup.style.display = typeSelect.value === 'wedding_anniversary' ? '' : 'none';
    }

    function syncFromMember() {
        if (!memberSelect || !typeSelect) return;
        var opt = memberSelect.selectedOptions[0];
        if (!opt || !opt.value) return;

        var type = typeSelect.value;
        var name = opt.textContent.split(' (')[0];
        if (titleInput && !titleInput.dataset.userEdited) {
            if (type === 'birthday') titleInput.value = name + "'s Birthday";
            else if (type === 'wedding_anniversary') titleInput.value = name + "'s Wedding Anniversary";
        }

        if (originalDate) {
            if (type === 'birthday' && opt.dataset.dob) originalDate.value = opt.dataset.dob;
            if (type === 'wedding_anniversary' && opt.dataset.wedding) originalDate.value = opt.dataset.wedding;
        }
    }

    if (titleInput) {
        titleInput.addEventListener('input', function () {
            titleInput.dataset.userEdited = '1';
        });
    }

    if (typeSelect) typeSelect.addEventListener('change', function () { toggleWeddingType(); syncFromMember(); });
    if (memberSelect) memberSelect.addEventListener('change', syncFromMember);
    toggleWeddingType();
})();
</script>
@endpush
