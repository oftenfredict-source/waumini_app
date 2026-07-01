@extends('layouts.church')

@section('title', __('pages.system_users.title'))

@section('content')
@include('church.system.partials.nav')

@include('partials.page-header', [
    'icon' => 'fa fa-users',
    'title' => __('pages.system_users.title'),
    'subtitle' => __('pages.system_users.subtitle', ['church' => $church->name]),
])

<div class="mb-3 text-md-right">
    <a href="{{ route('church.system.users.create') }}" class="btn btn-primary">
        <i class="fa fa-user-plus"></i> {{ __('pages.system_users.add_staff') }}
    </a>
</div>

<div class="tile">
    <div class="table-responsive">
        <table class="table table-hover table-bordered mb-0">
            <thead>
                <tr>
                    <th>{{ __('common.name') }}</th>
                    <th>{{ __('common.email') }}</th>
                    <th>{{ __('common.role') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th>{{ __('pages.shared.last_login') }}</th>
                    <th class="text-right">{{ __('common.actions') }}</th>
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
                        <td>{{ $user->last_login_at?->format('M d, Y H:i') ?? __('common.never') }}</td>
                        <td class="text-right">
                            <a href="{{ route('church.system.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">{{ __('common.edit') }}</a>
                            <form method="POST" action="{{ route('church.system.users.reset-password', $user) }}" class="d-inline"
                                data-swal-confirm="Reset password for this user?">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning">{{ __('pages.system_users.reset_password') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">{{ __('pages.system_users.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $users->links() }}</div>
</div>
@endsection
