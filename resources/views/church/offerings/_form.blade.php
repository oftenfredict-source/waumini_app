@php
    $offering = $offering ?? null;
    $defaultContribution = old('contribution_type');

    if ($defaultContribution === null) {
        if ($offering?->member_id) {
            $defaultContribution = 'member';
        } elseif ($offering?->church_service_id) {
            $defaultContribution = 'general';
        } else {
            $defaultContribution = 'member';
        }
    }
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

    <div class="col-md-6" id="memberSelectionGroup" @if($defaultContribution !== 'member') style="display:none;" @endif>
        @php
            $selectedMember = $members->firstWhere('id', (int) old('member_id', $offering?->member_id));
            $membersForSearch = $members
                ->sortBy(fn ($m) => sprintf('%s-%s', $m->envelope_number ? '0' : '1', $m->envelope_number ?? ''))
                ->values()
                ->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->full_name,
                    'envelope' => $m->envelope_number,
                ]);
        @endphp
        <div class="form-group position-relative">
            <label>{{ __('common.member') }} *</label>
            <input type="hidden" name="member_id" id="member_id"
                value="{{ old('member_id', $offering?->member_id) }}"
                @disabled($defaultContribution !== 'member')>
            <input type="text"
                id="member_envelope_search"
                class="form-control @error('member_id') is-invalid @enderror"
                autocomplete="off"
                placeholder="{{ __('pages.offerings.search_envelope') }}"
                value="{{ $selectedMember ? trim(($selectedMember->envelope_number ? $selectedMember->envelope_number.' — ' : '').$selectedMember->full_name) : '' }}"
                @disabled($defaultContribution !== 'member')>
            <div id="member_envelope_results" class="list-group position-absolute w-100 shadow-sm"
                style="display:none; z-index: 20; max-height: 260px; overflow-y: auto;"></div>
            <small class="text-muted">{{ __('pages.offerings.search_envelope_hint') }}</small>
            @error('member_id')<small class="text-danger d-block">{{ $message }}</small>@enderror
        </div>
        <script type="application/json" id="offeringMembersData">@json($membersForSearch)</script>
    </div>

    <div class="col-md-6" id="serviceSelectionGroup" @if($defaultContribution !== 'general') style="display:none;" @endif>
        <div class="form-group">
            <label>{{ __('pages.shared.service') }} *</label>
            <select name="church_service_id" id="church_service_id" class="form-control @error('church_service_id') is-invalid @enderror"
                @disabled($defaultContribution !== 'general')>
                <option value="">{{ __('pages.shared.select_service_dash') }}</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}"
                        data-service-date="{{ $service->service_date?->toDateString() }}"
                        @selected(old('church_service_id', $offering?->church_service_id) == $service->id)>
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
            <label>{{ __('pages.offerings.form_offering_type') }} *</label>
            @php
                $customOfferingTypes = $customOfferingTypes ?? [];
                $selectedType = old('offering_type');
                if ($selectedType === null) {
                    $selectedType = $offering?->offering_type?->value ?? 'general';
                    if (
                        $selectedType === \App\Enums\OfferingType::Other->value
                        && $offering?->offering_type_other
                        && in_array($offering->offering_type_other, $customOfferingTypes, true)
                    ) {
                        $selectedType = 'custom:'.$offering->offering_type_other;
                    }
                }
            @endphp
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
        <div class="form-group">
            <label>{{ __('pages.offerings.form_offering_type_other') }} *</label>
            <input type="text" name="offering_type_other" id="offering_type_other"
                class="form-control @error('offering_type_other') is-invalid @enderror"
                value="{{ old('offering_type_other', $offering?->offering_type_other) }}"
                placeholder="{{ __('pages.offerings.form_offering_type_other_placeholder') }}">
            @error('offering_type_other')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('pages.shared.amount_tzs') }} *</label>
            <input type="number" step="0.01" min="0.01" name="amount"
                class="form-control @error('amount') is-invalid @enderror"
                value="{{ old('amount', $offering?->amount) }}" required>
            @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('pages.offerings.form_offering_date') }} *</label>
            <input type="date" name="offering_date" id="offering_date" class="form-control @error('offering_date') is-invalid @enderror"
                value="{{ old('offering_date', $offering?->offering_date?->toDateString() ?? now()->toDateString()) }}" required>
            @error('offering_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ __('pages.shared.payment_method') }} *</label>
            <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                @foreach($paymentMethods as $method)
                    <option value="{{ $method->value }}" @selected(old('payment_method', $offering?->payment_method?->value) === $method->value)>
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
                value="{{ old('reference_number', $offering?->reference_number) }}">
            @error('reference_number')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label>{{ __('pages.shared.notes') }}</label>
            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $offering?->notes) }}</textarea>
            @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
</div>

@unless($offering)
    <div class="alert alert-info mb-0">
        <i class="fa fa-info-circle"></i>
        {!! __('pages.shared.pending_approval_alert', [
            'items' => __('pages.offerings.items'),
            'link' => '<a href="' . route('church.finance.approvals') . '">' . __('pages.shared.approval_dashboard') . '</a>',
        ]) !!}
    </div>
@endunless
