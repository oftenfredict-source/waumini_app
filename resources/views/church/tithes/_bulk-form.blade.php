@php
    $membersForSearch = $members
        ->values()
        ->map(fn ($m) => [
            'id' => $m->id,
            'name' => $m->full_name,
            'envelope' => $m->envelope_number,
        ]);

    $oldEntries = old('entries');
    if (! is_array($oldEntries) || count($oldEntries) === 0) {
        $oldEntries = [['member_id' => '', 'amount' => '', 'reference_number' => '', 'notes' => '']];
    }
@endphp

<div class="row mb-3">
    <div class="col-md-4">
        <div class="form-group mb-0">
            <label>{{ __('pages.tithes.form_tithe_date') }} *</label>
            <input type="date" name="tithe_date" class="form-control @error('tithe_date') is-invalid @enderror"
                value="{{ old('tithe_date', now()->toDateString()) }}" required>
            @error('tithe_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-0">
            <label>{{ __('pages.shared.payment_method') }} *</label>
            <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                @foreach($paymentMethods as $method)
                    <option value="{{ $method->value }}" @selected(old('payment_method', 'cash') === $method->value)>
                        {{ $method->label() }}
                    </option>
                @endforeach
            </select>
            @error('payment_method')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
</div>

<p class="text-muted mb-2">{{ __('pages.tithes.bulk_hint') }}</p>

<div id="titheEntries" class="mb-3">
    @foreach($oldEntries as $index => $entry)
        @php
            $selectedMember = $members->firstWhere('id', (int) ($entry['member_id'] ?? 0));
            $selectedLabel = $selectedMember
                ? ($selectedMember->envelope_number
                    ? $selectedMember->envelope_number.'-'.$selectedMember->full_name
                    : $selectedMember->full_name)
                : '';
        @endphp
        <div class="tithe-entry border rounded p-3 mb-3" data-entry-index="{{ $index }}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong class="entry-label">{{ __('pages.tithes.entry_label', ['number' => $index + 1]) }}</strong>
                <button type="button" class="btn btn-sm btn-outline-danger remove-entry" @if(count($oldEntries) === 1) style="display:none;" @endif>
                    <i class="fa fa-trash"></i> {{ __('pages.tithes.remove_entry') }}
                </button>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group position-relative mb-2 mb-md-0">
                        <label>{{ __('common.member') }} *</label>
                        <input type="hidden" name="entries[{{ $index }}][member_id]" class="entry-member-id"
                            value="{{ $entry['member_id'] ?? '' }}">
                        <input type="text"
                            class="form-control entry-member-search @error('entries.'.$index.'.member_id') is-invalid @enderror"
                            autocomplete="off"
                            placeholder="{{ __('pages.offerings.search_envelope') }}"
                            value="{{ $selectedLabel }}">
                        <div class="entry-member-results list-group position-absolute w-100 shadow-sm"
                            style="display:none; z-index: 20; max-height: 220px; overflow-y: auto;"></div>
                        @error('entries.'.$index.'.member_id')<small class="text-danger d-block">{{ $message }}</small>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-2 mb-md-0">
                        <label>{{ __('pages.shared.amount_tzs') }} *</label>
                        <input type="number" step="0.01" min="0.01" name="entries[{{ $index }}][amount]"
                            class="form-control @error('entries.'.$index.'.amount') is-invalid @enderror"
                            value="{{ $entry['amount'] ?? '' }}" required>
                        @error('entries.'.$index.'.amount')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
                <div class="col-md-4 entry-reference-group">
                    <div class="form-group mb-0">
                        <label>{{ __('pages.shared.reference_number') }}</label>
                        <input type="text" name="entries[{{ $index }}][reference_number]"
                            class="form-control entry-reference @error('entries.'.$index.'.reference_number') is-invalid @enderror"
                            value="{{ $entry['reference_number'] ?? '' }}">
                        @error('entries.'.$index.'.reference_number')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<button type="button" class="btn btn-outline-primary mb-3" id="addTitheEntry">
    <i class="fa fa-plus"></i> {{ __('pages.tithes.add_entry') }}
</button>

<div class="alert alert-info mb-0">
    <i class="fa fa-info-circle"></i>
    {!! __('pages.shared.pending_approval_alert', [
        'items' => __('pages.tithes.items'),
        'link' => '<a href="' . route('church.finance.approvals') . '">' . __('pages.shared.approval_dashboard') . '</a>',
    ]) !!}
</div>

<template id="titheEntryTemplate">
    <div class="tithe-entry border rounded p-3 mb-3" data-entry-index="__INDEX__">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="entry-label">{{ __('pages.tithes.entry_label', ['number' => '__NUMBER__']) }}</strong>
            <button type="button" class="btn btn-sm btn-outline-danger remove-entry">
                <i class="fa fa-trash"></i> {{ __('pages.tithes.remove_entry') }}
            </button>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="form-group position-relative mb-2 mb-md-0">
                    <label>{{ __('common.member') }} *</label>
                    <input type="hidden" name="entries[__INDEX__][member_id]" class="entry-member-id" value="">
                    <input type="text"
                        class="form-control entry-member-search"
                        autocomplete="off"
                        placeholder="{{ __('pages.offerings.search_envelope') }}"
                        value="">
                    <div class="entry-member-results list-group position-absolute w-100 shadow-sm"
                        style="display:none; z-index: 20; max-height: 220px; overflow-y: auto;"></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-2 mb-md-0">
                    <label>{{ __('pages.shared.amount_tzs') }} *</label>
                    <input type="number" step="0.01" min="0.01" name="entries[__INDEX__][amount]"
                        class="form-control" value="" required>
                </div>
            </div>
            <div class="col-md-4 entry-reference-group">
                <div class="form-group mb-0">
                    <label>{{ __('pages.shared.reference_number') }}</label>
                    <input type="text" name="entries[__INDEX__][reference_number]"
                        class="form-control entry-reference" value="">
                </div>
            </div>
        </div>
    </div>
</template>

<script type="application/json" id="titheMembersData">@json($membersForSearch)</script>
@error('entries')<div class="text-danger mt-2">{{ $message }}</div>@enderror
