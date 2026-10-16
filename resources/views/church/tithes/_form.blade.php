@php
    $tithe = $tithe ?? null;
    $selectedMember = $members->firstWhere('id', (int) old('member_id', $tithe?->member_id));
    $membersForSearch = $members
        ->values()
        ->map(fn ($m) => [
            'id' => $m->id,
            'name' => $m->full_name,
            'envelope' => $m->envelope_number,
        ]);
    $selectedMemberLabel = $selectedMember
        ? ($selectedMember->envelope_number
            ? $selectedMember->envelope_number.'-'.$selectedMember->full_name
            : $selectedMember->full_name)
        : '';
@endphp
<div class="row">
    <div class="col-md-6">
        <div class="form-group position-relative">
            <label>{{ __('common.member') }} *</label>
            <input type="hidden" name="member_id" id="tithe_member_id"
                value="{{ old('member_id', $tithe?->member_id) }}" required>
            <input type="text"
                id="tithe_member_search"
                class="form-control @error('member_id') is-invalid @enderror"
                autocomplete="off"
                placeholder="{{ __('pages.offerings.search_envelope') }}"
                value="{{ $selectedMemberLabel }}"
                required>
            <div id="tithe_member_results" class="list-group position-absolute w-100 shadow-sm"
                style="display:none; z-index: 20; max-height: 260px; overflow-y: auto;"></div>
            <small class="text-muted">{{ __('pages.offerings.search_envelope_hint') }}</small>
            @error('member_id')<small class="text-danger d-block">{{ $message }}</small>@enderror
        </div>
        <script type="application/json" id="titheMembersData">@json($membersForSearch)</script>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('pages.shared.amount_tzs') }} *</label>
            <input type="number" step="0.01" min="0.01" name="amount"
                class="form-control @error('amount') is-invalid @enderror"
                value="{{ old('amount', $tithe?->amount) }}" required>
            @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('pages.tithes.form_tithe_date') }} *</label>
            <input type="date" name="tithe_date" class="form-control @error('tithe_date') is-invalid @enderror"
                value="{{ old('tithe_date', $tithe?->tithe_date?->toDateString() ?? now()->toDateString()) }}" required>
            @error('tithe_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('pages.shared.payment_method') }} *</label>
            <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                @foreach($paymentMethods as $method)
                    <option value="{{ $method->value }}" @selected(old('payment_method', $tithe?->payment_method?->value) === $method->value)>
                        {{ $method->label() }}
                    </option>
                @endforeach
            </select>
            @error('payment_method')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4" id="referenceGroup">
        <div class="form-group">
            <label>{{ __('pages.shared.reference_number') }}</label>
            <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror"
                value="{{ old('reference_number', $tithe?->reference_number) }}">
            @error('reference_number')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label>{{ __('pages.shared.notes') }}</label>
            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $tithe?->notes) }}</textarea>
            @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
</div>
