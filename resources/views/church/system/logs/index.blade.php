@extends('layouts.church')

@section('title', __('pages.system_logs.title'))

@section('content')
@include('church.system.partials.nav')

@include('partials.page-header', [
    'icon' => 'fa fa-list-alt',
    'title' => __('pages.system_logs.title'),
    'subtitle' => __('pages.system_logs.subtitle', ['church' => $church->name]),
])

<div class="tile mb-3">
    <form method="GET" class="form-row">
        <div class="form-group col-md-3">
            <label>{{ __('pages.shared.action') }}</label>
            <select name="action" class="form-control">
                <option value="">{{ __('pages.shared.all_actions') }}</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-3">
            <label>{{ __('common.from') }}</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="form-group col-md-3">
            <label>{{ __('common.to') }}</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="form-group col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> {{ __('common.filter') }}</button>
        </div>
    </form>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead>
                <tr>
                    <th>{{ __('common.date') }}</th>
                    <th>{{ __('common.name') }}</th>
                    <th>{{ __('pages.shared.action') }}</th>
                    <th>IP</th>
                    <th>{{ __('common.details') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at?->format('M d, Y H:i') }}</td>
                        <td>{{ $log->user?->name ?? __('pages.shared.system') }}</td>
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
                    <tr><td colspan="5" class="text-center text-muted py-4">{{ __('pages.system_logs.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $logs->links() }}</div>
</div>
@endsection
