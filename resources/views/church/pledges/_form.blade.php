@php
    $pledge = $pledge ?? null;
@endphp
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('common.member') }} *</label>
            <select name="member_id" class="form-control @error('member_id') is-invalid @enderror" required>
                <option value="">{{ __('pages.shared.select_member') }}</option>
                @foreach($members as $member)
                    <option value="{{ $member->id }}" @selected(old('member_id', $pledge?->member_id) == $member->id)>
                        {{ $member->full_name }}@if($member->envelope_number) ({{ $member->envelope_number }})@endif
                    </option>
                @endforeach
            </select>
            @error('member_id')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('pages.pledges.form_pledge_type') }} *</label>
            <select name="pledge_type" id="pledge_type" class="form-control @error('pledge_type') is-invalid @enderror" required>
                @foreach($pledgeTypes as $type)
                    <option value="{{ $type->value }}" @selected(old('pledge_type', $pledge?->pledge_type?->value ?? 'general') === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>
            @error('pledge_type')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-3" id="pledgeTypeOtherGroup" style="display: none;">
        <div class="form-group">
            <label>{{ __('pages.pledges.form_pledge_type_other') }} *</label>
            <input type="text" name="pledge_type_other" id="pledge_type_other"
                class="form-control @error('pledge_type_other') is-invalid @enderror"
                value="{{ old('pledge_type_other', $pledge?->pledge_type_other) }}"
                placeholder="{{ __('pages.pledges.form_pledge_type_other_placeholder') }}">
            @error('pledge_type_other')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('pages.pledges.form_pledge_amount') }} *</label>
            <input type="number" step="0.01" min="0.01" name="pledge_amount"
                class="form-control @error('pledge_amount') is-invalid @enderror"
                value="{{ old('pledge_amount', $pledge?->pledge_amount) }}" required>
            @error('pledge_amount')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('pages.pledges.form_pledge_date') }} *</label>
            <input type="date" name="pledge_date" class="form-control @error('pledge_date') is-invalid @enderror"
                value="{{ old('pledge_date', $pledge?->pledge_date?->toDateString() ?? now()->toDateString()) }}" required>
            @error('pledge_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ __('pages.pledges.form_payment_frequency') }} *</label>
            <select name="payment_frequency" id="payment_frequency" class="form-control @error('payment_frequency') is-invalid @enderror" required>
                @foreach($frequencies as $frequency)
                    <option value="{{ $frequency->value }}" @selected(old('payment_frequency', $pledge?->payment_frequency?->value ?? 'monthly') === $frequency->value)>
                        {{ $frequency->label() }}
                    </option>
                @endforeach
            </select>
            @error('payment_frequency')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-3" id="oneTimeDateGroup" style="display: none;">
        <div class="form-group">
            <label>{{ __('pages.pledges.form_one_time_date') }} *</label>
            <input type="date" name="one_time_payment_date" id="one_time_payment_date"
                class="form-control @error('one_time_payment_date') is-invalid @enderror"
                value="{{ old('one_time_payment_date', $pledge?->payment_frequency?->value === 'one_time' ? $pledge?->due_date?->toDateString() : '') }}">
            @error('one_time_payment_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label>{{ __('pages.shared.purpose') }}</label>
            <input type="text" name="purpose" class="form-control @error('purpose') is-invalid @enderror"
                value="{{ old('purpose', $pledge?->purpose) }}" placeholder="{{ __('pages.pledges.form_purpose_placeholder') }}">
            @error('purpose')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label>{{ __('pages.shared.notes') }}</label>
            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $pledge?->notes) }}</textarea>
            @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
</div>

@unless($pledge)
    <div class="alert alert-info mb-0">
        <i class="fa fa-info-circle"></i>
        {!! __('pages.shared.pledge_active_alert', [
            'link' => '<a href="' . route('church.finance.approvals') . '">' . __('pages.shared.approval_dashboard') . '</a>',
        ]) !!}
    </div>
@endunless
