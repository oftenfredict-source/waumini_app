@extends('layouts.church')

@section('title', 'Manage Users')

@section('content')
@include('church.system.partials.nav')

<div class="app-title">
    <div>
        <h1><i class="fa fa-users"></i> Manage Users</h1>
        <p>Church staff accounts for {{ $church->name }}</p>
    </div>
    <div class="text-right">
        <a href="{{ route('church.system.users.create') }}" class="btn btn-primary">
            <i class="fa fa-user-plus"></i> Add Staff User
        </a>
    </div>
</div>


<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover table-bordered mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge badge-primary">{{ ucfirst($role->name) }}</span>
                            @endforeach
                        </td>
                        <td>
                            <span class="badge badge-{{ $user->status->value === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($user->status->value) }}
                            </span>
                        </td>
                        <td>{{ $user->last_login_at?->format('M d, Y H:i') ?? 'Never' }}</td>
                        <td class="text-right">
                            <a href="{{ route('church.system.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="POST" action="{{ route('church.system.users.reset-password', $user) }}" class="d-inline"
                                data-swal-confirm="Reset password for this user?">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning">Reset Password</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No staff users yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $users->links() }}</div>
</div>
@endsection
