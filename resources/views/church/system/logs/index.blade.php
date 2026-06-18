@extends('layouts.church')

@section('title', 'System Logs')

@section('content')
@include('church.system.partials.nav')

<div class="app-title">
    <div>
        <h1><i class="fa fa-list-alt"></i> Logs</h1>
        <p>Audit trail for {{ $church->name }}</p>
    </div>
</div>

<div class="tile mb-3">
    <form method="GET" class="form-row">
        <div class="form-group col-md-3">
            <label>Action</label>
            <select name="action" class="form-control">
                <option value="">All actions</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-3">
            <label>From</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="form-group col-md-3">
            <label>To</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="form-group col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Filter</button>
        </div>
    </form>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>IP</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at?->format('M d, Y H:i') }}</td>
                        <td>{{ $log->user?->name ?? 'System' }}</td>
                        <td><code>{{ $log->action }}</code></td>
                        <td>{{ $log->ip_address ?? '—' }}</td>
                        <td>
                            @if($log->new_values)
                                <small class="text-muted">{{ Str::limit(json_encode($log->new_values), 80) }}</small>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No logs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $logs->links() }}</div>
</div>
@endsection
