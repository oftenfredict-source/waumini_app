@extends('layouts.owner')

@section('title', $church->name)

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-building"></i> {{ $church->name }}</h1>
        <p>Church profile and management</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.churches.index') }}">Churches</a></li>
        <li class="breadcrumb-item">{{ $church->name }}</li>
    </ul>
</div>


<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">Church Details</h3>
            <table class="table table-borderless">
                <tr><th width="180">Status</th><td>@include('owner.components.status-badge', ['status' => $church->status])</td></tr>
                <tr><th>Email</th><td>{{ $church->email }}</td></tr>
                <tr><th>Phone</th><td>{{ $church->phone ?? '—' }}</td></tr>
                <tr><th>Pastor</th><td>{{ $church->pastor_name ?? '—' }}</td></tr>
                <tr><th>Denomination</th><td>{{ $church->denomination ?? '—' }}</td></tr>
                <tr><th>Location</th><td>{{ collect([$church->city, $church->country])->filter()->implode(', ') ?: '—' }}</td></tr>
                <tr><th>Address</th><td>{{ $church->address ?? '—' }}</td></tr>
                <tr><th>Subdomain</th><td><a href="{{ $church->subdomainUrl() }}" target="_blank" rel="noopener noreferrer"><code>{{ $church->tenantDomain() }}</code></a></td></tr>
                <tr><th>Branches</th><td>{{ $church->branches_enabled ? 'Enabled' : 'Disabled' }}</td></tr>
                <tr><th>Trial Ends</th><td>{{ $church->trial_ends_at?->format('M d, Y H:i') ?? '—' }}</td></tr>
                <tr><th>Registered</th><td>{{ $church->created_at->format('M d, Y H:i') }}</td></tr>
            </table>
        </div>

        @if($church->activeSubscription)
            <div class="tile">
                <h3 class="tile-title">Active Subscription</h3>
                <table class="table table-borderless">
                    <tr><th width="180">Package</th><td>{{ $church->activeSubscription->package?->name }}</td></tr>
                    <tr><th>Billing</th><td>{{ ucfirst($church->activeSubscription->billing_cycle->value) }}</td></tr>
                    <tr><th>Status</th><td>{{ ucfirst($church->activeSubscription->status->value) }}</td></tr>
                    <tr><th>Ends</th><td>{{ $church->activeSubscription->ends_at?->format('M d, Y') ?? '—' }}</td></tr>
                </table>
            </div>
        @endif

        <div class="tile">
            <h3 class="tile-title">Admin Login</h3>
            @if($church->adminUser)
                <table class="table table-borderless">
                    <tr>
                        <th width="180">Username (Email)</th>
                        <td><code>{{ $church->adminUser->email }}</code></td>
                    </tr>
                    <tr>
                        <th>Admin Name</th>
                        <td>{{ $church->adminUser->name }}</td>
                    </tr>
                    <tr>
                        <th>Login URL</th>
                        <td>
                            <a href="{{ $church->subdomainUrl('/login') }}" target="_blank" rel="noopener noreferrer">{{ $church->subdomainUrl('/login') }}</a>
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge badge-{{ $church->adminUser->status->value === 'active' ? 'success' : 'danger' }}">
                                {{ ucfirst($church->adminUser->status->value) }}
                            </span>
                        </td>
                    </tr>
                </table>
                <p class="text-muted mb-2"><i class="fa fa-info-circle"></i> Password is auto-generated and stored securely.</p>
                @can('manageAdmin', $church)
                    <form method="POST" action="{{ route('owner.churches.regenerate-admin-password', $church) }}"
                        data-swal-confirm="Generate a new password? The old password will stop working immediately.">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-refresh"></i> Reset admin password
                        </button>
                    </form>
                @endcan
            @else
                <p class="text-muted mb-2">No admin account linked to this church yet.</p>
                @can('manageAdmin', $church)
                    <form method="POST" action="{{ route('owner.churches.create-admin', $church) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa fa-user-plus"></i> Create admin account
                        </button>
                    </form>
                @endcan
            @endif
        </div>
    </div>

    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">Actions</h3>
            <div class="d-flex flex-column">
                <a href="{{ route('owner.churches.edit', $church) }}" class="btn btn-warning btn-block mb-2">
                    <i class="fa fa-edit"></i> Edit Church
                </a>

                @if($church->status === \App\Enums\ChurchStatus::Suspended)
                    @can('activate', $church)
                        <form method="POST" action="{{ route('owner.churches.activate', $church) }}">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block mb-2">
                                <i class="fa fa-check"></i> Activate Church
                            </button>
                        </form>
                    @endcan
                @else
                    @can('suspend', $church)
                        <form method="POST" action="{{ route('owner.churches.suspend', $church) }}"
                            data-swal-confirm="Suspend this church?"
                            data-swal-delete>
                            @csrf
                            <input type="hidden" name="reason" value="Suspended by owner admin">
                            <button type="submit" class="btn btn-warning btn-block mb-2">
                                <i class="fa fa-pause"></i> Suspend Church
                            </button>
                        </form>
                    @endcan
                @endif

                @can('delete', $church)
                    <form method="POST" action="{{ route('owner.churches.destroy', $church) }}"
                        data-swal-confirm="Delete this church permanently?"
                        data-swal-delete
                        data-swal-confirm-text="Yes, delete">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fa fa-trash"></i> Delete Church
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        @if($church->suspended_reason)
            <div class="tile">
                <h3 class="tile-title">Suspension Reason</h3>
                <p class="text-danger">{{ $church->suspended_reason }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
