@extends('layouts.owner')

@section('title', $church->name)

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-building"></i> {{ $church->name }}</h1>
        <p>{{ __('owner.church.show_subtitle') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.churches.index') }}">{{ __('owner.churches') }}</a></li>
        <li class="breadcrumb-item">{{ $church->name }}</li>
    </ul>
</div>


<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <h3 class="tile-title">{{ __('owner.church.church_details') }}</h3>
            <table class="table table-borderless">
                <tr><th width="180">{{ __('owner.status') }}</th><td>@include('owner.components.status-badge', ['status' => $church->status])</td></tr>
                <tr><th>{{ __('owner.email') }}</th><td>{{ $church->email }}</td></tr>
                <tr><th>{{ __('owner.phone') }}</th><td>{{ $church->phone ?? '—' }}</td></tr>
                <tr><th>{{ __('owner.church.pastor') }}</th><td>{{ $church->pastor_name ?? '—' }}</td></tr>
                <tr><th>{{ __('owner.church.denomination') }}</th><td>{{ $church->denomination ?? '—' }}</td></tr>
                <tr><th>{{ __('owner.church.location') }}</th><td>{{ collect([$church->city, $church->country])->filter()->implode(', ') ?: '—' }}</td></tr>
                <tr><th>{{ __('owner.church.address') }}</th><td>{{ $church->address ?? '—' }}</td></tr>
                <tr><th>{{ __('owner.church.subdomain') }}</th><td><a href="{{ $church->subdomainUrl() }}" target="_blank" rel="noopener noreferrer"><code>{{ $church->tenantDomain() }}</code></a></td></tr>
                <tr><th>{{ __('owner.church.member_id_prefix') }}</th><td><code>{{ strtoupper(data_get($church->settings, 'member_id_prefix', '—')) }}</code> <small class="text-muted">(e.g. {{ strtoupper(data_get($church->settings, 'member_id_prefix', 'IM')) }}-{{ now()->format('Y') }}-0001)</small></td></tr>
                <tr><th>{{ __('owner.church.branches') }}</th><td>{{ $church->branches_enabled ? __('owner.church.enabled') : __('owner.church.disabled') }}</td></tr>
                <tr><th>{{ __('owner.church.trial_ends') }}</th><td>{{ $church->trial_ends_at?->format('M d, Y H:i') ?? '—' }}</td></tr>
                <tr><th>{{ __('owner.church.registered') }}</th><td>{{ $church->created_at->format('M d, Y H:i') }}</td></tr>
            </table>
        </div>

        @if($church->activeSubscription)
            <div class="tile">
                <h3 class="tile-title">{{ __('owner.church.active_subscription') }}</h3>
                <table class="table table-borderless">
                    <tr><th width="180">{{ __('owner.package') }}</th><td>{{ $church->activeSubscription->package?->name }}</td></tr>
                    <tr><th>{{ __('owner.church.billing') }}</th><td>{{ ucfirst($church->activeSubscription->billing_cycle->value) }}</td></tr>
                    <tr><th>{{ __('owner.status') }}</th><td>{{ ucfirst($church->activeSubscription->status->value) }}</td></tr>
                    <tr><th>{{ __('owner.subs.ends_trial') }}</th><td>{{ $church->activeSubscription->ends_at?->format('M d, Y') ?? '—' }}</td></tr>
                </table>
            </div>
        @endif

        <div class="tile">
            <h3 class="tile-title">{{ __('owner.church.admin_login') }}</h3>
            @if($church->adminUser)
                <table class="table table-borderless">
                    <tr>
                        <th width="180">{{ __('owner.church.username_email') }}</th>
                        <td><code>{{ $church->adminUser->email }}</code></td>
                    </tr>
                    <tr>
                        <th>{{ __('owner.church.admin_name') }}</th>
                        <td>{{ $church->adminUser->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('owner.church.login_url') }}</th>
                        <td>
                            <a href="{{ $church->portalUrl('/login') }}" target="_blank" rel="noopener noreferrer">{{ $church->portalUrl('/login') }}</a>
                            @unless(config('waumini.use_subdomain_urls'))
                                <br><small class="text-muted">{{ __('owner.church.subdomain_dns') }} <code>{{ $church->subdomainUrl('/login') }}</code></small>
                            @endunless
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('owner.status') }}</th>
                        <td>
                            <span class="badge badge-{{ $church->adminUser->status->value === 'active' ? 'success' : 'danger' }}">
                                {{ ucfirst($church->adminUser->status->value) }}
                            </span>
                        </td>
                    </tr>
                </table>
                <p class="text-muted mb-2"><i class="fa fa-info-circle"></i> {{ __('owner.church.password_secure') }}</p>
                @can('manageAdmin', $church)
                    <form method="POST" action="{{ route('owner.churches.regenerate-admin-password', $church) }}"
                        data-swal-confirm="{{ __('owner.church.reset_confirm') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-refresh"></i> {{ __('owner.church.reset_password') }}
                        </button>
                    </form>
                @endcan
            @else
                <p class="text-muted mb-2">{{ __('owner.church.no_admin') }}</p>
                @can('manageAdmin', $church)
                    <form method="POST" action="{{ route('owner.churches.create-admin', $church) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa fa-user-plus"></i> {{ __('owner.church.create_admin') }}
                        </button>
                    </form>
                @endcan
            @endif
        </div>
    </div>

    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('owner.church.actions') }}</h3>
            <div class="d-flex flex-column">
                @can('impersonate', $church)
                    @if($church->adminUser)
                        <form method="POST" action="{{ route('owner.churches.impersonate', $church) }}"
                            data-swal-confirm="{{ __('owner.church.enter_church_confirm') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-block mb-2">
                                <i class="fa fa-sign-in"></i> {{ __('owner.church.enter_church') }}
                            </button>
                        </form>
                    @endif
                @endcan

                <a href="{{ route('owner.churches.edit', $church) }}" class="btn btn-warning btn-block mb-2">
                    <i class="fa fa-edit"></i> {{ __('owner.church.edit_church') }}
                </a>

                @if($church->status === \App\Enums\ChurchStatus::Suspended)
                    @can('activate', $church)
                        <form method="POST" action="{{ route('owner.churches.activate', $church) }}">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block mb-2">
                                <i class="fa fa-check"></i> {{ __('owner.church.activate') }}
                            </button>
                        </form>
                    @endcan
                @else
                    @can('suspend', $church)
                        <form method="POST" action="{{ route('owner.churches.suspend', $church) }}"
                            data-swal-confirm="{{ __('owner.church.suspend_confirm') }}"
                            data-swal-delete>
                            @csrf
                            <input type="hidden" name="reason" value="{{ __('owner.church.suspended_by') }}">
                            <button type="submit" class="btn btn-warning btn-block mb-2">
                                <i class="fa fa-pause"></i> {{ __('owner.church.suspend') }}
                            </button>
                        </form>
                    @endcan
                @endif

                @can('delete', $church)
                    <form method="POST" action="{{ route('owner.churches.destroy', $church) }}"
                        data-swal-confirm="{{ __('owner.church.delete_confirm') }}"
                        data-swal-delete
                        data-swal-confirm-text="{{ __('common.yes_delete') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fa fa-trash"></i> {{ __('owner.church.delete') }}
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        @if($church->suspended_reason)
            <div class="tile">
                <h3 class="tile-title">{{ __('owner.church.suspension_reason') }}</h3>
                <p class="text-danger">{{ $church->suspended_reason }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
