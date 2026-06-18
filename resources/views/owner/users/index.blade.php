@extends('layouts.owner')

@section('title', 'Users & Roles')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-users"></i> Users & Roles</h1>
        <p>Platform staff and role-based access</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">Overview</a></li>
        <li class="breadcrumb-item">Users</li>
    </ul>
</div>

<div class="row mb-3">
    @foreach($roles as $role)
        <div class="col-md-3">
            <div class="widget-small primary coloured-icon">
                <i class="icon fa fa-shield fa-3x"></i>
                <div class="info">
                    <h4>{{ ucfirst($role->name) }}</h4>
                    <p><b>{{ $role->users_count }} users</b></p>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="tile">
    <h3 class="tile-title">Platform Users</h3>
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th>Last Login</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ucfirst($user->user_type->value) }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge badge-primary">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            <span class="badge badge-{{ $user->status->value === 'active' ? 'success' : 'danger' }}">
                                {{ ucfirst($user->status->value) }}
                            </span>
                        </td>
                        <td>{{ $user->last_login_at?->format('M d, Y H:i') ?? 'Never' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>
@endsection
