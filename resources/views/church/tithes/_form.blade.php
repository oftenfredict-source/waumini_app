@php
    $tithe = $tithe ?? null;
@endphp
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Member *</label>
            <select name="member_id" class="form-control @error('member_id') is-invalid @enderror" required>
                <option value="">Select member</option>
                @foreach($members as $member)
                    <option value="{{ $member->id }}" @selected(old('member_id', $tithe?->member_id) == $member->id)>
                        {{ $member->full_name }}@if($member->envelope_number) ({{ $member->envelope_number }})@endif
                    </option>
                @endforeach
            </select>
            @error('member_id')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Amount (TZS) *</label>
            <input type="number" step="0.01" min="0.01" name="amount"
                class="form-control @error('amount') is-invalid @enderror"
                value="{{ old('amount', $tithe?->amount) }}" required>
            @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Tithe Date *</label>
            <input type="date" name="tithe_date" class="form-control @error('tithe_date') is-invalid @enderror"
                value="{{ old('tithe_date', $tithe?->tithe_date?->toDateString() ?? now()->toDateString()) }}" required>
            @error('tithe_date')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Payment Method *</label>
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
            <label>Reference Number</label>
            <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror"
                value="{{ old('reference_number', $tithe?->reference_number) }}">
            @error('reference_number')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $tithe?->notes) }}</textarea>
            @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
</div>

@unless($tithe)
    <div class="alert alert-info mb-0">
        <i class="fa fa-info-circle"></i>
        New tithes are submitted as <strong>pending</strong> and must be approved on the
        <a href="{{ route('church.finance.approvals') }}">Approval Dashboard</a> before they count toward finance reports.
    </div>
@endunless
