@php
    $backup = $backupSettings ?? [];
    $logs = $backupLogs ?? collect();
    $connected = ! empty($backup['connected']);
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-1">{{ __('owner.set.backup_heading') }}</h4>
        <p class="text-muted mb-0">{{ __('owner.set.backup_help') }}</p>
    </div>
    <div class="mt-2 mt-md-0">
        @if($connected)
            <span class="badge badge-success p-2 mr-1">
                <i class="fa fa-check"></i>
                {{ __('owner.set.backup_connected_badge') }}
            </span>
        @endif
        <a href="{{ route('owner.settings.backup.google.connect') }}" class="btn btn-sm btn-success">
            <i class="fa fa-google"></i> {{ $connected ? __('owner.set.backup_reconnect') : __('owner.set.backup_connect') }}
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

    <h5 class="mb-3"><i class="fa fa-cogs"></i> {{ __('owner.set.backup_advanced') }}</h5>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.backup_dump_path') }}</label>
                <input type="text" name="dump_path" class="form-control" value="{{ old('dump_path', $backup['dump_path'] ?? '') }}" placeholder="C:\xampp\mysql\bin">
                <small class="text-muted">{{ __('owner.set.backup_dump_path_help') }}</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.backup_client_id') }}</label>
                <input type="text" name="google_client_id" class="form-control" value="{{ old('google_client_id', $backup['client_id'] ?? '') }}" autocomplete="off">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.backup_client_secret') }}</label>
                <input type="password" name="google_client_secret" class="form-control" value="" autocomplete="new-password" placeholder="{{ ! empty($backup['has_client_secret']) ? __('owner.set.keep_password') : __('owner.set.backup_client_secret_placeholder') }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.backup_folder_id') }}</label>
                <input type="text" name="folder_id" class="form-control" value="{{ old('folder_id', $backup['folder_id'] ?? '') }}" placeholder="waumini_backup">
                <small class="text-muted">{{ __('owner.set.backup_folder_id_help') }}</small>
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-group">
                <label>{{ __('owner.set.backup_refresh_token') }}</label>
                <input type="text" name="google_refresh_token" class="form-control" value="{{ old('google_refresh_token', $backup['refresh_token'] ?? '') }}" autocomplete="off" placeholder="{{ __('owner.set.backup_refresh_token_placeholder') }}">
                <small class="text-muted">{{ __('owner.set.backup_refresh_token_help') }}</small>
            </div>
        </div>
    </div>

    <h5 class="mb-3 mt-4"><i class="fa fa-sliders"></i> {{ __('owner.set.backup_basic') }}</h5>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.backup_name') }}</label>
                <input type="text" name="backup_name" class="form-control" value="{{ old('backup_name', $backup['backup_name'] ?? 'waumini_backup') }}" placeholder="waumini_backup">
                <small class="text-muted">{{ __('owner.set.backup_name_help') }}</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.backup_notify_email') }}</label>
                <input type="email" name="notify_email" class="form-control" value="{{ old('notify_email', $backup['notify_email'] ?? '') }}" placeholder="oftenfred.ict@gmail.com">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.backup_notify_phone') }}</label>
                <input type="text" name="notify_phone" class="form-control" value="{{ old('notify_phone', $backup['notify_phone'] ?? '') }}" placeholder="0744341239">
                <small class="text-muted">{{ __('owner.set.backup_notify_phone_help') }}</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.backup_run_at') }}</label>
                <input type="time" name="run_at" class="form-control" value="{{ old('run_at', $backup['run_at'] ?? '02:00') }}" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>{{ __('owner.set.backup_keep_count') }}</label>
                <input type="number" name="keep_count" class="form-control" value="{{ old('keep_count', $backup['keep_count'] ?? 14) }}" min="1" max="90" required>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $backup['enabled'] ?? false))>
                <span class="label-text">{{ __('owner.set.backup_enabled') }}</span>
            </label>
        </div>
    </div>
    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="notify_sms" value="1" @checked(old('notify_sms', $backup['notify_sms'] ?? true))>
                <span class="label-text">{{ __('owner.set.backup_notify_sms') }}</span>
            </label>
        </div>
    </div>

    @if(empty($backup['sms_gateway_ready']))
        <div class="alert alert-warning py-2">
            <i class="fa fa-exclamation-triangle"></i>
            {{ __('owner.set.backup_notify_gateway') }}
        </div>
    @endif

    <div class="alert alert-warning">
        {{ __('owner.set.backup_scheduler_help') }}
        <code class="d-block mt-2">php artisan schedule:run</code>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="fa fa-save"></i> {{ __('owner.set.save_backup') }}
    </button>
    <button type="submit" name="backup_action" value="run" class="btn btn-success ml-2" onclick="return confirm(@json(__('owner.set.backup_run_confirm')));">
        <i class="fa fa-cloud-upload"></i> {{ __('owner.set.backup_run_now') }}
    </button>
</form>

<h5 class="mt-4">{{ __('owner.set.backup_history') }}</h5>
@if($logs->isEmpty())
    <p class="text-muted">{{ __('owner.set.backup_history_empty') }}</p>
@else
    <div class="table-responsive">
        <table class="table table-sm table-striped">
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
                        <td>{{ $log->started_at?->format('Y-m-d H:i') }}</td>
                        <td><code>{{ $log->filename }}</code></td>
                        <td>{{ $log->size_bytes ? number_format($log->size_bytes / 1048576, 2).' MB' : '—' }}</td>
                        <td>
                            @if($log->isSuccess())
                                <span class="badge badge-success">{{ __('owner.set.backup_ok') }}</span>
                            @else
                                <span class="badge badge-danger">{{ __('owner.set.backup_failed') }}</span>
                            @endif
                        </td>
                        <td>{{ $log->message ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
