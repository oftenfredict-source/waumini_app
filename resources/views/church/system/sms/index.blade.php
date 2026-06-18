@extends('layouts.church')

@section('title', 'SMS Store')

@section('content')
@include('church.system.partials.nav')

<div class="app-title">
    <div>
        <h1><i class="fa fa-commenting"></i> SMS Store</h1>
        <p>Manage message templates and all SMS sent from {{ $church->name }}</p>
    </div>
</div>

@if(!$platformSmsEnabled)
    <div class="tile mb-3">
        <div class="alert alert-warning mb-0">
            <i class="fa fa-exclamation-triangle"></i>
            Platform SMS is not configured or disabled. Messages are stored but may not be delivered until SMS is enabled.
        </div>
    </div>
@elseif(!$smsEnabled)
    <div class="tile mb-3">
        <div class="alert alert-info mb-0">
            <i class="fa fa-info-circle"></i>
            Church SMS notifications are disabled.
            <a href="{{ route('church.system.settings.index', ['tab' => 'notifications']) }}">Enable in Settings → Notifications</a>.
        </div>
    </div>
@endif

<div class="row mb-3">
    <div class="col-md-3">
        <div class="tile text-center">
            <h4 class="mb-0">{{ $stats['total'] }}</h4>
            <small class="text-muted">Total Stored</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="tile text-center">
            <h4 class="mb-0 text-success">{{ $stats['sent'] }}</h4>
            <small class="text-muted">Sent</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="tile text-center">
            <h4 class="mb-0 text-danger">{{ $stats['failed'] }}</h4>
            <small class="text-muted">Failed</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="tile text-center">
            <h4 class="mb-0">{{ $stats['this_month'] }}</h4>
            <small class="text-muted">Sent This Month</small>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link @if($tab === 'templates') active @endif" href="{{ route('church.system.sms.index', ['tab' => 'templates']) }}">
            <i class="fa fa-file-text-o"></i> Message Templates
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if($tab === 'messages') active @endif" href="{{ route('church.system.sms.index', ['tab' => 'messages']) }}">
            <i class="fa fa-history"></i> Sent Messages
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if($tab === 'compose') active @endif" href="{{ route('church.system.sms.index', ['tab' => 'compose']) }}">
            <i class="fa fa-pencil"></i> Compose SMS
        </a>
    </li>
</ul>

@if($tab === 'templates')
    <div class="tile">
        <p class="text-muted mb-3">
            Edit the default text used when the system sends SMS. Use placeholders shown for each template — they are replaced automatically when sending.
        </p>
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th>Template</th>
                        <th>Placeholders</th>
                        <th>Status</th>
                        <th width="100"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $item)
                        <tr>
                            <td>
                                <strong>{{ $item['label'] }}</strong>
                                @if($item['description'])
                                    <br><small class="text-muted">{{ $item['description'] }}</small>
                                @endif
                            </td>
                            <td>
                                @foreach($item['placeholders'] as $placeholder)
                                    <code class="mr-1">{{ $placeholder }}</code>
                                @endforeach
                            </td>
                            <td>
                                @if($item['is_active'])
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('church.system.sms.templates.edit', $item['key']) }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@elseif($tab === 'messages')
    <div class="tile mb-3">
        <form method="GET" class="form-row">
            <input type="hidden" name="tab" value="messages">
            <div class="form-group col-md-2">
                <label>Status</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">All</option>
                    <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                    <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                    <option value="skipped" @selected(request('status') === 'skipped')>Skipped</option>
                </select>
            </div>
            <div class="form-group col-md-2">
                <label>Type</label>
                <select name="context" class="form-control form-control-sm">
                    <option value="">All types</option>
                    @foreach($contexts as $context)
                        <option value="{{ $context }}" @selected(request('context') === $context)>{{ config('sms_templates.context_labels.'.$context, $context) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                <label>From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="form-group col-md-2">
                <label>To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="form-group col-md-3">
                <label>Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Phone or message…" value="{{ request('search') }}">
            </div>
            <div class="form-group col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fa fa-filter"></i></button>
            </div>
        </form>
    </div>

    <div class="tile">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Recipient</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th width="80"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $sms)
                        <tr>
                            <td nowrap>{{ $sms->created_at?->format('M d, Y H:i') }}</td>
                            <td>{{ $sms->recipient }}</td>
                            <td><span class="badge badge-light">{{ $sms->contextLabel() }}</span></td>
                            <td>
                                <span title="{{ $sms->message }}">{{ \Illuminate\Support\Str::limit($sms->message, 60) }}</span>
                                @if($sms->edited_at)
                                    <br><small class="text-muted">Edited {{ $sms->edited_at->format('M d, H:i') }}</small>
                                @endif
                            </td>
                            <td><span class="badge badge-{{ $sms->statusBadgeClass() }}">{{ ucfirst($sms->status) }}</span></td>
                            <td>
                                <a href="{{ route('church.system.sms.messages.show', $sms) }}" class="btn btn-sm btn-info" title="View & edit">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted text-center py-4">No SMS messages stored yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($messages->hasPages())
            <div class="mt-3">{{ $messages->links() }}</div>
        @endif
    </div>
@else
    <div class="tile">
        <h4 class="mb-3">Compose & Send SMS</h4>
        <form method="POST" action="{{ route('church.system.sms.send') }}">
            @csrf
            <div class="form-group">
                <label>Recipient Phone <span class="text-danger">*</span></label>
                <input type="text" name="recipient" class="form-control @error('recipient') is-invalid @enderror"
                    value="{{ old('recipient') }}" placeholder="+255712345678" required>
                @error('recipient')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Message <span class="text-danger">*</span></label>
                <textarea name="message" rows="6" class="form-control @error('message') is-invalid @enderror"
                    placeholder="Type your SMS message…" required maxlength="1000">{{ old('message') }}</textarea>
                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Maximum 1000 characters. The message is saved in the SMS store after sending.</small>
            </div>
            <button type="submit" class="btn btn-success">
                <i class="fa fa-paper-plane"></i> Send SMS
            </button>
        </form>
    </div>
@endif
@endsection
