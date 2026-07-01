@extends('layouts.church')

@section('title', __('pages.system_sessions.title'))

@section('content')
@include('church.system.partials.nav')

@include('partials.page-header', [
    'icon' => 'fa fa-user-circle',
    'title' => __('pages.system_sessions.title'),
    'subtitle' => __('pages.system_sessions.subtitle'),
])

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('common.name') }}</th>
                    <th>{{ __('common.email') }}</th>
                    <th>{{ __('common.role') }}</th>
                    <th>{{ __('pages.shared.last_activity') }}</th>
                    <th>IP</th>
                    <th class="text-right">{{ __('pages.shared.action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td>
                            {{ $session->name }}
                            @if($session->is_current)
                                <span class="badge badge-info">{{ __('pages.shared.you') }}</span>
                            @endif
                        </td>
                        <td>{{ $session->email }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $session->user_type)) }}</td>
                        <td>{{ $session->last_activity_human }}</td>
                        <td>{{ $session->ip_address ?? '—' }}</td>
                        <td class="text-right">
                            @if(! $session->is_current)
                                <form method="POST" action="{{ route('church.system.sessions.revoke', $session->id) }}" class="d-inline"
                                    data-swal-confirm="Revoke this session?"
                                    data-swal-delete>
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('common.revoke') }}</button>
                                </form>
                            @else
                                <span class="text-muted">{{ __('common.current') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">{{ __('pages.system_sessions.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
