@extends('layouts.church')

@section('title', 'SMS Message')

@section('content')
@include('church.system.partials.nav')

<div class="app-title">
    <div>
        <h1><i class="fa fa-comment"></i> SMS Message</h1>
        <p>{{ $smsLog->contextLabel() }} — {{ $smsLog->created_at?->format('M d, Y H:i') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.system.sms.index', ['tab' => 'messages']) }}">SMS Store</a></li>
        <li class="breadcrumb-item">Message</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h4 class="tile-title">Edit Message</h4>
            <form method="POST" action="{{ route('church.system.sms.messages.update', $smsLog) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Recipient</label>
                    <input type="text" name="recipient" class="form-control @error('recipient') is-invalid @enderror"
                        value="{{ old('recipient', $smsLog->recipient) }}" required>
                    @error('recipient')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" rows="8" class="form-control @error('message') is-invalid @enderror" required maxlength="1000">{{ old('message', $smsLog->message) }}</textarea>
                    @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('church.system.sms.index', ['tab' => 'messages']) }}" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>

        <div class="tile">
            <h4 class="tile-title">Resend</h4>
            <p class="text-muted">Send this message again using the current text above. Save any edits first.</p>
            <form method="POST" action="{{ route('church.system.sms.messages.resend', $smsLog) }}">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fa fa-paper-plane"></i> Resend SMS
                </button>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h5 class="tile-title">Details</h5>
            <table class="table table-sm table-borderless mb-0">
                <tr><th>Type</th><td>{{ $smsLog->contextLabel() }}</td></tr>
                <tr><th>Status</th><td><span class="badge badge-{{ $smsLog->statusBadgeClass() }}">{{ ucfirst($smsLog->status) }}</span></td></tr>
                <tr><th>Sent At</th><td>{{ $smsLog->created_at?->format('M d, Y H:i') ?? '—' }}</td></tr>
                @if($smsLog->edited_at)
                    <tr><th>Edited</th><td>{{ $smsLog->edited_at->format('M d, Y H:i') }}@if($smsLog->editor) by {{ $smsLog->editor->name }}@endif</td></tr>
                @endif
                @if($smsLog->provider_response)
                    <tr><th>Provider</th><td><small>{{ $smsLog->provider_response }}</small></td></tr>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
