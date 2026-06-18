@extends('layouts.church')

@section('title', 'System Monitor')

@section('content')
@include('church.system.partials.nav')

<div class="app-title">
    <div>
        <h1><i class="fa fa-server"></i> System Monitor</h1>
        <p>Health and activity overview for {{ $church->name }}</p>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-users fa-3x"></i>
            <div class="info"><h4>Staff Users</h4><p><b>{{ $stats['staff_users'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-user fa-3x"></i>
            <div class="info"><h4>Portal Users</h4><p><b>{{ $stats['portal_users'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-check-square-o fa-3x"></i>
            <div class="info"><h4>Active Sessions</h4><p><b>{{ $stats['active_sessions'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="widget-small danger coloured-icon">
            <i class="icon fa fa-list-alt fa-3x"></i>
            <div class="info"><h4>Logs (7 days)</h4><p><b>{{ $stats['audit_logs_7d'] }}</b></p></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title">Environment</h3>
            <table class="table table-sm table-borderless mb-0">
                <tr><th width="180">PHP Version</th><td>{{ $stats['php_version'] }}</td></tr>
                <tr><th>Laravel</th><td>{{ $stats['laravel_version'] }}</td></tr>
                <tr><th>Database</th>
                    <td>
                        <span class="badge badge-{{ $stats['database']['status'] === 'connected' ? 'success' : 'danger' }}">
                            {{ ucfirst($stats['database']['status']) }} ({{ $stats['database']['driver'] }})
                        </span>
                    </td>
                </tr>
                <tr><th>Cache Driver</th><td>{{ $stats['cache'] }}</td></tr>
                <tr><th>Session Driver</th><td>{{ $stats['session_driver'] }}</td></tr>
                <tr><th>Timezone</th><td>{{ $stats['timezone'] }}</td></tr>
                <tr><th>Total Members</th><td>{{ number_format($stats['members']) }}</td></tr>
                <tr><th>Total Audit Logs</th><td>{{ number_format($stats['audit_logs_total']) }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title">Top Actions (7 days)</h3>
            @if($stats['recent_actions']->isNotEmpty())
                <table class="table table-sm mb-0">
                    <thead><tr><th>Action</th><th class="text-right">Count</th></tr></thead>
                    <tbody>
                        @foreach($stats['recent_actions'] as $row)
                            <tr>
                                <td><code>{{ $row->action }}</code></td>
                                <td class="text-right">{{ $row->total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted mb-0">No audit activity in the last 7 days.</p>
            @endif
        </div>
    </div>
</div>
@endsection
