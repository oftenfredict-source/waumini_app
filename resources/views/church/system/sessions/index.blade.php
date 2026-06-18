@extends('layouts.church')

@section('title', 'User Sessions')

@section('content')
@include('church.system.partials.nav')

<div class="app-title">
    <div>
        <h1><i class="fa fa-user-circle"></i> User Sessions</h1>
        <p>Active sessions in the last 24 hours</p>
    </div>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Last Activity</th>
                    <th>IP</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td>
                            {{ $session->name }}
                            @if($session->is_current)
                                <span class="badge badge-info">You</span>
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
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Revoke</button>
                                </form>
                            @else
                                <span class="text-muted">Current</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No active sessions.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
