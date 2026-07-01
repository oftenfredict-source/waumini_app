@extends('layouts.church')

@section('title', __('pages.system_sms.title'))

@section('content')
@include('church.system.partials.nav')

@include('partials.page-header', [
    'icon' => 'fa fa-commenting',
    'title' => __('pages.system_sms.title'),
    'subtitle' => __('pages.system_sms.subtitle', ['church' => $church->name]),
])

@if(!$platformSmsEnabled)
    <div class="tile mb-3">
        <div class="alert alert-warning mb-0">
            <i class="fa fa-exclamation-triangle"></i>
            {{ __('pages.system_sms.platform_disabled') }}
        </div>
    </div>
@elseif(!$smsEnabled)
    <div class="tile mb-3">
        <div class="alert alert-info mb-0">
            <i class="fa fa-info-circle"></i>
            {{ __('pages.system_sms.church_disabled') }}
            <a href="{{ route('church.system.settings.index', ['tab' => 'notifications']) }}">{{ __('pages.system_sms.enable_notifications') }}</a>.
        </div>
    </div>
@endif

<div class="row mb-3">
    <div class="col-md-3">
        <div class="tile text-center">
            <h4 class="mb-0">{{ $stats['total'] }}</h4>
            <small class="text-muted">{{ __('pages.system_sms.total_stored') }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="tile text-center">
            <h4 class="mb-0 text-success">{{ $stats['sent'] }}</h4>
            <small class="text-muted">{{ __('pages.system_sms.sent') }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="tile text-center">
            <h4 class="mb-0 text-danger">{{ $stats['failed'] }}</h4>
            <small class="text-muted">{{ __('pages.system_sms.failed') }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="tile text-center">
            <h4 class="mb-0">{{ $stats['this_month_segments'] }}</h4>
            <small class="text-muted">{{ __('pages.system_sms.sent_this_month_units') }}</small>
            <div class="small text-muted mt-1">{{ __('pages.system_sms.sent_this_month_messages', ['count' => $stats['this_month_messages']]) }}</div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link @if($tab === 'templates') active @endif" href="{{ route('church.system.sms.index', ['tab' => 'templates']) }}">
            <i class="fa fa-file-text-o"></i> {{ __('pages.system_sms.templates_tab') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if($tab === 'messages') active @endif" href="{{ route('church.system.sms.index', ['tab' => 'messages']) }}">
            <i class="fa fa-history"></i> {{ __('pages.system_sms.messages_tab') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link @if($tab === 'compose') active @endif" href="{{ route('church.system.sms.index', ['tab' => 'compose']) }}">
            <i class="fa fa-pencil"></i> {{ __('pages.system_sms.compose_tab') }}
        </a>
    </li>
</ul>

@if($tab === 'templates')
    <div class="tile">
        <p class="text-muted mb-3">
            {{ __('pages.system_sms.templates_intro') }}
        </p>
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th>{{ __('pages.system_sms.template') }}</th>
                        <th>{{ __('pages.system_sms.placeholders') }}</th>
                        <th>{{ __('common.status') }}</th>
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
                                    <span class="badge badge-success">{{ __('common.active') }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ __('common.inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('church.system.sms.templates.edit', $item['key']) }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-pencil"></i> {{ __('common.edit') }}
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
                <label>{{ __('common.status') }}</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">{{ __('common.all') }}</option>
                    <option value="sent" @selected(request('status') === 'sent')>{{ __('pages.system_sms.sent') }}</option>
                    <option value="failed" @selected(request('status') === 'failed')>{{ __('pages.system_sms.failed') }}</option>
                    <option value="skipped" @selected(request('status') === 'skipped')>{{ __('pages.system_sms.skipped') }}</option>
                </select>
            </div>
            <div class="form-group col-md-2">
                <label>{{ __('common.type') }}</label>
                <select name="context" class="form-control form-control-sm">
                    <option value="">{{ __('pages.shared.all_types') }}</option>
                    @foreach($contexts as $context)
                        <option value="{{ $context }}" @selected(request('context') === $context)>{{ config('sms_templates.context_labels.'.$context, $context) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                <label>{{ __('common.from') }}</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="form-group col-md-2">
                <label>{{ __('common.to') }}</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="form-group col-md-3">
                <label>{{ __('common.search') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="{{ __('pages.system_sms.search_placeholder') }}" value="{{ request('search') }}">
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
                        <th>{{ __('common.date') }}</th>
                        <th>{{ __('pages.system_sms.recipient') }}</th>
                        <th>{{ __('common.type') }}</th>
                        <th>{{ __('pages.system_sms.message_col') }}</th>
                        <th>{{ __('common.status') }}</th>
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
                                    <br><small class="text-muted">{{ __('pages.system_sms.edited_at', ['datetime' => $sms->edited_at->format('M d, H:i')]) }}</small>
                                @endif
                            </td>
                            <td><span class="badge badge-{{ $sms->statusBadgeClass() }}">{{ ucfirst($sms->status) }}</span></td>
                            <td>
                                <a href="{{ route('church.system.sms.messages.show', $sms) }}" class="btn btn-sm btn-info" title="{{ __('pages.system_sms.view_edit') }}">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted text-center py-4">{{ __('pages.system_sms.no_messages') }}</td>
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
        <h4 class="mb-3">{{ __('pages.system_sms.compose_heading') }}</h4>
        <form method="POST" action="{{ route('church.system.sms.send') }}">
            @csrf
            <div class="form-group">
                <label>{{ __('pages.system_sms.recipient_phone') }} <span class="text-danger">*</span></label>
                <input type="text" name="recipient" class="form-control @error('recipient') is-invalid @enderror"
                    value="{{ old('recipient') }}" placeholder="{{ __('pages.system_sms.recipient_placeholder') }}" required>
                @error('recipient')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>{{ __('pages.system_sms.message_col') }} <span class="text-danger">*</span></label>
                <textarea name="message" rows="6" class="form-control @error('message') is-invalid @enderror"
                    placeholder="{{ __('pages.system_sms.message_placeholder') }}" required maxlength="1000">{{ old('message') }}</textarea>
                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">{{ __('pages.system_sms.max_chars_hint') }}</small>
            </div>
            <button type="submit" class="btn btn-success">
                <i class="fa fa-paper-plane"></i> {{ __('pages.system_sms.send_sms') }}
            </button>
        </form>
    </div>
@endif
@endsection
