@php
    $backup = $backupSettings ?? [];
    $logs = $backupLogs ?? collect();
    $connected = ! empty($backup['connected']);
@endphp

<form method="POST" action="{{ route('owner.settings.backup') }}">
    @csrf
    @method('PUT')
    <h4 class="mb-3">{{ __('owner.set.backup_heading') }}</h4>
    <p class="text-muted">{{ __('owner.set.backup_help') }}</p>

    <div class="alert alert-info">
        <strong>{{ __('owner.set.backup_setup_title') }}</strong>
        <ol class="mb-0 mt-2 pl-3">
            <li>{{ __('owner.set.backup_step_1') }}</li>
            <li>{{ __('owner.set.backup_step_2') }}</li>
            <li>{{ __('owner.set.backup_step_3') }}</li>
            <li>{{ __('owner.set.backup_step_4') }}</li>
        </ol>
    </div>

    <div class="form-group">
        <label>{{ __('owner.set.backup_redirect_uri') }}</label>
        <input type="text" class="form-control" value="{{ $backup['redirect_uri'] ?? '' }}" readonly onclick="this.select()">
        <small class="text-muted">{{ __('owner.set.backup_redirect_uri_help') }}</small>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('owner.set.backup_client_id') }}</label>
                <input type="text" name="google_client_id" class="form-control" value="{{ old('google_client_id', $backup['client_id'] ?? '') }}" autocomplete="off">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('owner.set.backup_client_secret') }}</label>
                <input type="password" name="google_client_secret" class="form-control" value="" autocomplete="new-password" placeholder="{{ ! empty($backup['has_client_secret']) ? __('owner.set.keep_password') : __('owner.set.backup_client_secret_placeholder') }}">
            </div>
        </div>
    </div>
    <div class="form-group">
        <label>{{ __('owner.set.backup_refresh_token') }}</label>
        <input type="password" name="google_refresh_token" class="form-control" value="" autocomplete="new-password" placeholder="{{ ! empty($backup['has_refresh_token']) ? __('owner.set.keep_password') : __('owner.set.backup_refresh_token_placeholder') }}">
        <small class="text-muted">{{ __('owner.set.backup_refresh_token_help') }}</small>
    </div>

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $backup['enabled'] ?? false))>
                <span class="label-text">{{ __('owner.set.backup_enabled') }}</span>
            </label>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ __('owner.set.backup_folder_id') }}</label>
                <input type="text" name="folder_id" class="form-control" value="{{ old('folder_id', $backup['folder_id'] ?? '') }}" placeholder="1AbCDefGhijKLmnoPQ">
                <small class="text-muted">{{ __('owner.set.backup_folder_id_help') }}</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>{{ __('owner.set.backup_run_at') }}</label>
                <input type="time" name="run_at" class="form-control" value="{{ old('run_at', $backup['run_at'] ?? '02:00') }}" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>{{ __('owner.set.backup_keep_count') }}</label>
                <input type="number" name="keep_count" class="form-control" value="{{ old('keep_count', $backup['keep_count'] ?? 14) }}" min="1" max="90" required>
            </div>
        </div>
    </div>

    <hr class="my-3">
    <h5>{{ __('owner.set.backup_notify_heading') }}</h5>
    <p class="text-muted">{{ __('owner.set.backup_notify_help') }}</p>

    @if(empty($backup['sms_gateway_ready']))
        <div class="alert alert-warning py-2">
            <i class="fa fa-exclamation-triangle"></i>
            {{ __('owner.set.backup_notify_gateway') }}
        </div>
    @endif

    <div class="form-group">
        <div class="animated-checkbox">
            <label>
                <input type="checkbox" name="notify_sms" value="1" @checked(old('notify_sms', $backup['notify_sms'] ?? false))>
                <span class="label-text">{{ __('owner.set.backup_notify_sms') }}</span>
            </label>
        </div>
    </div>
    <div class="form-group">
        <label>{{ __('owner.set.backup_notify_phone') }}</label>
        <input type="text" name="notify_phone" class="form-control" value="{{ old('notify_phone', $backup['notify_phone'] ?? '') }}" placeholder="255614863345">
        <small class="text-muted">{{ __('owner.set.backup_notify_phone_help') }}</small>
    </div>

    <div class="alert alert-warning">
        {{ __('owner.set.backup_scheduler_help') }}
        <code class="d-block mt-2">php artisan schedule:run</code>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('owner.set.save_backup') }}</button>
</form>

<hr class="my-4">

<h5>{{ __('owner.set.backup_connect_heading') }}</h5>
@if($connected)
    <p class="text-success mb-3">
        <i class="fa fa-check-circle"></i>
        @if(! empty($backup['connected_email']))
            {{ __('owner.set.backup_connected', ['email' => $backup['connected_email']]) }}
        @else
            {{ __('owner.set.backup_connected_token') }}
        @endif
    </p>
    <form method="POST" action="{{ route('owner.settings.backup.google.disconnect') }}" class="d-inline" onsubmit="return confirm(@json(__('owner.set.backup_disconnect_confirm')));">
        @csrf
        <button type="submit" class="btn btn-outline-danger">
            <i class="fa fa-unlink"></i> {{ __('owner.set.backup_disconnect') }}
        </button>
    </form>
@else
    <p class="text-muted">{{ __('owner.set.backup_connect_help') }}</p>
    <a href="{{ route('owner.settings.backup.google.connect') }}" class="btn btn-success">
        <i class="fa fa-google"></i> {{ __('owner.set.backup_connect') }}
    </a>
@endif

<hr class="my-4">

<form method="POST" action="{{ route('owner.settings.backup.run') }}" onsubmit="return confirm(@json(__('owner.set.backup_run_confirm')));">
    @csrf
    <button type="submit" class="btn btn-outline-primary" @disabled(empty($backup['configured']))>
        <i class="fa fa-cloud-upload"></i> {{ __('owner.set.backup_run_now') }}
    </button>
    @if(empty($backup['configured']))
        <small class="text-muted ml-2">{{ __('owner.set.backup_run_blocked') }}</small>
    @endif
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
