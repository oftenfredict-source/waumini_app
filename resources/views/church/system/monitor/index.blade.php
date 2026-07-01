@extends('layouts.church')

@section('title', __('pages.system_monitor.title'))

@section('content')
@include('church.system.partials.nav')

@include('partials.page-header', [
    'icon' => 'fa fa-server',
    'title' => __('pages.system_monitor.title'),
    'subtitle' => __('pages.system_monitor.subtitle', ['church' => $church->name]),
])

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-users fa-3x"></i>
            <div class="info"><h4>{{ __('pages.system_monitor.staff_users') }}</h4><p><b>{{ $stats['staff_users'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-user fa-3x"></i>
            <div class="info"><h4>{{ __('pages.system_monitor.portal_users') }}</h4><p><b>{{ $stats['portal_users'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-check-square-o fa-3x"></i>
            <div class="info"><h4>{{ __('pages.system_monitor.active_sessions') }}</h4><p><b>{{ $stats['active_sessions'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="widget-small danger coloured-icon">
            <i class="icon fa fa-list-alt fa-3x"></i>
            <div class="info"><h4>{{ __('pages.system_monitor.logs_7d') }}</h4><p><b>{{ $stats['audit_logs_7d'] }}</b></p></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.system_monitor.environment') }}</h3>
            <table class="table table-sm table-borderless mb-0">
                <tr><th width="180">{{ __('pages.system_monitor.php_version') }}</th><td>{{ $stats['php_version'] }}</td></tr>
                <tr><th>{{ __('pages.system_monitor.laravel') }}</th><td>{{ $stats['laravel_version'] }}</td></tr>
                <tr><th>{{ __('pages.system_monitor.database') }}</th>
                    <td>
                        <span class="badge badge-{{ $stats['database']['status'] === 'connected' ? 'success' : 'danger' }}">
                            {{ ucfirst($stats['database']['status']) }} ({{ $stats['database']['driver'] }})
                        </span>
                    </td>
                </tr>
                <tr><th>{{ __('pages.system_monitor.cache_driver') }}</th><td>{{ $stats['cache'] }}</td></tr>
                <tr><th>{{ __('pages.system_monitor.session_driver') }}</th><td>{{ $stats['session_driver'] }}</td></tr>
                <tr><th>{{ __('pages.system_monitor.timezone') }}</th><td>{{ $stats['timezone'] }}</td></tr>
                <tr><th>{{ __('pages.system_monitor.total_members') }}</th><td>{{ number_format($stats['members']) }}</td></tr>
                <tr><th>{{ __('pages.system_monitor.total_audit_logs') }}</th><td>{{ number_format($stats['audit_logs_total']) }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.system_monitor.top_actions') }}</h3>
            @if($stats['recent_actions']->isNotEmpty())
                <table class="table table-sm mb-0">
                    <thead><tr><th>{{ __('pages.shared.action') }}</th><th class="text-right">{{ __('pages.shared.count') }}</th></tr></thead>
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
                <p class="text-muted mb-0">{{ __('pages.system_monitor.no_audit_7d') }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
