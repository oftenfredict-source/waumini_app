@php
    $defaultContribution = old('contribution_type', 'member');
    $membersForSearch = $members
        ->values()
        ->map(fn ($m) => [
            'id' => $m->id,
            'name' => $m->full_name,
            'envelope' => $m->envelope_number,
        ]);

    $oldEntries = old('entries');
    if (! is_array($oldEntries) || count($oldEntries) === 0) {
        $oldEntries = [['member_id' => '', 'amount' => '', 'reference_number' => '']];
    }

    $customOfferingTypes = $customOfferingTypes ?? [];
    $selectedType = old('offering_type', 'general');
@endphp

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label class="d-block">{{ __('pages.offerings.form_offering_for') }} *</label>
            <div class="btn-group btn-group-toggle mb-2" id="contributionTypeToggle" data-toggle="buttons">
                @foreach($contributionTypes as $type)
                    <label class="btn btn-outline-primary {{ $defaultContribution === $type->value ? 'active' : '' }}">
                        <input type="radio" name="contribution_type" value="{{ $type->value }}"
                            id="contribution_type_{{ $type->value }}"
                            @checked($defaultContribution === $type->value)>
                        @if($type->value === 'member')
                            <i class="fa fa-user"></i>
                        @else
                            <i class="fa fa-users"></i>
                        @endif
                        {{ $type->label() }}
                    </label>
                @endforeach
            </div>
            @error('contribution_type')<small class="text-danger d-block">{{ $message }}</small>@enderror
            <small class="text-muted d-block" id="contributionHelpMember" @if($defaultContribution !== 'member') style="display:none;" @endif>
                {{ __('pages.offerings.form_help_member') }}
            </small>
            <small class="text-muted d-block" id="contributionHelpGeneral" @if($defaultContribution !== 'general') style="display:none;" @endif>
                {{ __('pages.offerings.form_help_general') }}
            </small>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <div class="form-group mb-0">
            <label>{{ __('pages.offerings.form_offering_date') }} *</label>
            <input type="date" name="offering_date" id="offering_date" class="form-control @error('offering_date') is-invalid @enderror"
                value="{{ old('offering_date', now()->toDateString()) }}" required>
            @error('offering_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-3">
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
    <div class="col-md-3">
        <div class="form-group mb-0">
            <label>{{ __('pages.offerings.form_offering_type') }} *</label>
            <select name="offering_type" id="offering_type" class="form-control @error('offering_type') is-invalid @enderror" required>
                @foreach($offeringTypes as $type)
                    @continue($type === \App\Enums\OfferingType::Other)
                    <option value="{{ $type->value }}" @selected($selectedType === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
                @foreach($customOfferingTypes as $customType)
                    <option value="custom:{{ $customType }}" @selected($selectedType === 'custom:'.$customType)>
                        {{ $customType }}
                    </option>
                @endforeach
                <option value="other" @selected($selectedType === 'other')>
                    {{ \App\Enums\OfferingType::Other->label() }}
                </option>
            </select>
            @error('offering_type')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-3" id="offeringTypeOtherGroup" style="display: none;">
        <div class="form-group mb-0">
            <label>{{ __('pages.offerings.form_offering_type_other') }} *</label>
            <input type="text" name="offering_type_other" id="offering_type_other"
                class="form-control @error('offering_type_other') is-invalid @enderror"
                value="{{ old('offering_type_other') }}"
                placeholder="{{ __('pages.offerings.form_offering_type_other_placeholder') }}">
            @error('offering_type_other')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
</div>

<div id="memberBulkGroup" @if($defaultContribution !== 'member') style="display:none;" @endif>
    <p class="text-muted mb-2">{{ __('pages.offerings.bulk_hint') }}</p>

    <div id="offeringEntries" class="mb-3">
        @foreach($oldEntries as $index => $entry)
            @php
                $selectedMember = $members->firstWhere('id', (int) ($entry['member_id'] ?? 0));
                $selectedLabel = $selectedMember
                    ? ($selectedMember->envelope_number
                        ? $selectedMember->envelope_number.'-'.$selectedMember->full_name
                        : $selectedMember->full_name)
                    : '';
            @endphp
            <div class="offering-entry border rounded p-3 mb-3" data-entry-index="{{ $index }}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="entry-label">{{ __('pages.offerings.entry_label', ['number' => $index + 1]) }}</strong>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-entry" @if(count($oldEntries) === 1) style="display:none;" @endif>
                        <i class="fa fa-trash"></i> {{ __('pages.offerings.remove_entry') }}
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
                                class="form-control entry-amount @error('entries.'.$index.'.amount') is-invalid @enderror"
                                value="{{ $entry['amount'] ?? '' }}">
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

    <button type="button" class="btn btn-outline-primary mb-3" id="addOfferingEntry">
        <i class="fa fa-plus"></i> {{ __('pages.offerings.add_entry') }}
    </button>
</div>

<div id="generalOfferingGroup" @if($defaultContribution !== 'general') style="display:none;" @endif>
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('pages.shared.service') }} *</label>
                <select name="church_service_id" id="church_service_id" class="form-control @error('church_service_id') is-invalid @enderror"
                    @disabled($defaultContribution !== 'general')>
                    <option value="">{{ __('pages.shared.select_service_dash') }}</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}"
                            data-service-date="{{ $service->service_date?->toDateString() }}"
                            @selected(old('church_service_id') == $service->id)>
                            {{ $service->offeringSelectionLabel() }}
                        </option>
                    @endforeach
                </select>
                @error('church_service_id')<small class="text-danger">{{ $message }}</small>@enderror
                <small class="text-muted">{{ __('pages.offerings.form_service_hint') }}</small>
                @if($services->isEmpty())
                    <small class="text-warning d-block">
                        {!! __('pages.offerings.form_no_services', [
                            'link' => '<a href="' . route('church.services.create') . '">' . __('pages.offerings.form_schedule_service') . '</a>',
                        ]) !!}
                    </small>
                @endif
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>{{ __('pages.shared.amount_tzs') }} *</label>
                <input type="number" step="0.01" min="0.01" name="amount" id="general_amount"
                    class="form-control @error('amount') is-invalid @enderror"
                    value="{{ old('amount') }}"
                    @disabled($defaultContribution !== 'general')>
                @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
        <div class="col-md-3" id="generalReferenceGroup">
            <div class="form-group">
                <label>{{ __('pages.shared.reference_number') }}</label>
                <input type="text" name="reference_number" id="general_reference"
                    class="form-control @error('reference_number') is-invalid @enderror"
                    value="{{ old('reference_number') }}"
                    @disabled($defaultContribution !== 'general')>
                @error('reference_number')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="form-group mb-0">
            <label>{{ __('pages.shared.notes') }}</label>
            <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
            @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
</div>

<div class="alert alert-info mb-0">
    <i class="fa fa-info-circle"></i>
    {!! __('pages.shared.pending_approval_alert', [
        'items' => __('pages.offerings.items'),
        'link' => '<a href="' . route('church.finance.approvals') . '">' . __('pages.shared.approval_dashboard') . '</a>',
    ]) !!}
</div>

<template id="offeringEntryTemplate">
    <div class="offering-entry border rounded p-3 mb-3" data-entry-index="__INDEX__">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong class="entry-label">{{ __('pages.offerings.entry_label', ['number' => '__NUMBER__']) }}</strong>
            <button type="button" class="btn btn-sm btn-outline-danger remove-entry">
                <i class="fa fa-trash"></i> {{ __('pages.offerings.remove_entry') }}
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
                        class="form-control entry-amount" value="">
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

<script type="application/json" id="offeringMembersData">@json($membersForSearch)</script>
@error('entries')<div class="text-danger mt-2">{{ $message }}</div>@enderror
