@php
    $backup = $backupSettings ?? [];
    $logs = $backupLogs ?? collect();
    $connected = ! empty($backup['connected']);
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="h5 mb-1">{{ __('owner.set.backup_heading') }}</h2>
        <p class="text-muted small mb-0">{{ __('owner.set.backup_help') }}</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        @if($connected)
            <span class="badge rounded-pill" style="background:rgba(22,163,74,.12);color:#16a34a;">
                <i class="bi bi-check-lg me-1"></i>{{ __('owner.set.backup_connected_badge') }}
            </span>
        @endif
        <a href="{{ route('owner.settings.backup.google.connect') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-google me-1"></i> {{ $connected ? __('owner.set.backup_reconnect') : __('owner.set.backup_connect') }}
        </a>
        @if($connected)
            <form method="POST" action="{{ route('owner.settings.backup.google.disconnect') }}" class="d-inline" onsubmit="return confirm(@json(__('owner.set.backup_disconnect_confirm')));">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('owner.set.backup_disconnect') }}</button>
            </form>
        @endif
    </div>
</div>

<form method="POST" action="{{ route('owner.settings.backup') }}">
    @csrf
    @method('PUT')

    <h3 class="h6 mb-3"><i class="bi bi-gear me-1"></i> {{ __('owner.set.backup_advanced') }}</h3>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="dump_path">{{ __('owner.set.backup_dump_path') }}</label>
            <input type="text" id="dump_path" name="dump_path" class="form-control" value="{{ old('dump_path', $backup['dump_path'] ?? '') }}" placeholder="C:\xampp\mysql\bin">
            <div class="form-text">{{ __('owner.set.backup_dump_path_help') }}</div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="google_client_id">{{ __('owner.set.backup_client_id') }}</label>
            <input type="text" id="google_client_id" name="google_client_id" class="form-control" value="{{ old('google_client_id', $backup['client_id'] ?? '') }}" autocomplete="off">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="google_client_secret">{{ __('owner.set.backup_client_secret') }}</label>
            <input type="password" id="google_client_secret" name="google_client_secret" class="form-control" value="" autocomplete="new-password" placeholder="{{ ! empty($backup['has_client_secret']) ? __('owner.set.keep_password') : __('owner.set.backup_client_secret_placeholder') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="folder_id">{{ __('owner.set.backup_folder_id') }}</label>
            <input type="text" id="folder_id" name="folder_id" class="form-control" value="{{ old('folder_id', $backup['folder_id'] ?? '') }}" placeholder="waumini_backup">
            <div class="form-text">{{ __('owner.set.backup_folder_id_help') }}</div>
        </div>
        <div class="col-md-8">
            <label class="form-label" for="google_refresh_token">{{ __('owner.set.backup_refresh_token') }}</label>
            <input type="text" id="google_refresh_token" name="google_refresh_token" class="form-control" value="{{ old('google_refresh_token', $backup['refresh_token'] ?? '') }}" autocomplete="off" placeholder="{{ __('owner.set.backup_refresh_token_placeholder') }}">
            <div class="form-text">{{ __('owner.set.backup_refresh_token_help') }}</div>
        </div>
    </div>

    <h3 class="h6 mb-3 mt-4"><i class="bi bi-sliders me-1"></i> {{ __('owner.set.backup_basic') }}</h3>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="backup_name">{{ __('owner.set.backup_name') }}</label>
            <input type="text" id="backup_name" name="backup_name" class="form-control" value="{{ old('backup_name', $backup['backup_name'] ?? 'waumini_backup') }}" placeholder="waumini_backup">
            <div class="form-text">{{ __('owner.set.backup_name_help') }}</div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="notify_email">{{ __('owner.set.backup_notify_email') }}</label>
            <input type="email" id="notify_email" name="notify_email" class="form-control" value="{{ old('notify_email', $backup['notify_email'] ?? '') }}" placeholder="oftenfred.ict@gmail.com">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="notify_phone">{{ __('owner.set.backup_notify_phone') }}</label>
            <input type="text" id="notify_phone" name="notify_phone" class="form-control" value="{{ old('notify_phone', $backup['notify_phone'] ?? '') }}" placeholder="0744341239">
            <div class="form-text">{{ __('owner.set.backup_notify_phone_help') }}</div>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="run_at">{{ __('owner.set.backup_run_at') }}</label>
            <input type="time" id="run_at" name="run_at" class="form-control" value="{{ old('run_at', $backup['run_at'] ?? '02:00') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="keep_count">{{ __('owner.set.backup_keep_count') }}</label>
            <input type="number" id="keep_count" name="keep_count" class="form-control" value="{{ old('keep_count', $backup['keep_count'] ?? 14) }}" min="1" max="90" required>
        </div>
    </div>

    <div class="form-check mt-3 mb-2">
        <input type="checkbox" class="form-check-input" id="enabled" name="enabled" value="1" @checked(old('enabled', $backup['enabled'] ?? false))>
        <label class="form-check-label" for="enabled">{{ __('owner.set.backup_enabled') }}</label>
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="notify_sms" name="notify_sms" value="1" @checked(old('notify_sms', $backup['notify_sms'] ?? true))>
        <label class="form-check-label" for="notify_sms">{{ __('owner.set.backup_notify_sms') }}</label>
    </div>

    @if(empty($backup['sms_gateway_ready']))
        <div class="alert border-0 mb-3" style="background:rgba(217,119,6,.12);color:#92400e;">
            <i class="bi bi-exclamation-triangle me-1"></i>
            {{ __('owner.set.backup_notify_gateway') }}
        </div>
    @endif

    <div class="alert border-0 mb-4" style="background:rgba(148,0,0,.06);color:#4a1a1a;">
        {{ __('owner.set.backup_scheduler_help') }}
        <code class="d-block mt-2">php artisan schedule:run</code>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> {{ __('owner.set.save_backup') }}
        </button>
        <button type="submit" name="backup_action" value="run" class="btn btn-outline-primary" onclick="return confirm(@json(__('owner.set.backup_run_confirm')));">
            <i class="bi bi-cloud-arrow-up me-1"></i> {{ __('owner.set.backup_run_now') }}
        </button>
    </div>
</form>

<h3 class="h6 mt-5 mb-3">{{ __('owner.set.backup_history') }}</h3>
@if($logs->isEmpty())
    <p class="text-muted mb-0">{{ __('owner.set.backup_history_empty') }}</p>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>{{ __('owner.set.backup_col_time') }}</th>
                    <th>{{ __('owner.set.backup_col_file') }}</th>
                    <th>{{ __('owner.set.backup_col_size') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th>{{ __('owner.set.backup_col_message') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td class="text-nowrap">{{ $log->started_at?->format('Y-m-d H:i') }}</td>
                        <td><code class="small">{{ $log->filename }}</code></td>
                        <td>{{ $log->size_bytes ? number_format($log->size_bytes / 1048576, 2).' MB' : '—' }}</td>
                        <td>
                            @if($log->isSuccess())
                                <span class="badge rounded-pill" style="background:rgba(22,163,74,.12);color:#16a34a;">{{ __('owner.set.backup_ok') }}</span>
                            @else
                                <span class="badge rounded-pill" style="background:rgba(220,38,38,.12);color:#dc2626;">{{ __('owner.set.backup_failed') }}</span>
                            @endif
                        </td>
                        <td>{{ $log->message ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
