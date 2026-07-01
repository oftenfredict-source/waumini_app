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
        <div class="form-group">
            <label>{{ __('common.member') }} *</label>
            <select name="member_id" id="member_id" class="form-control @error('member_id') is-invalid @enderror"
                @disabled($defaultContribution !== 'member')>
                <option value="">{{ __('pages.shared.select_member_dash') }}</option>
                @foreach($members as $member)
                    <option value="{{ $member->id }}" @selected(old('member_id', $offering?->member_id) == $member->id)>
                        {{ $member->full_name }}@if($member->envelope_number) ({{ $member->envelope_number }})@endif
                    </option>
                @endforeach
            </select>
            @error('member_id')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
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
            <select name="offering_type" id="offering_type" class="form-control @error('offering_type') is-invalid @enderror" required>
                @foreach($offeringTypes as $type)
                    <option value="{{ $type->value }}" @selected(old('offering_type', $offering?->offering_type?->value ?? 'general') === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
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
