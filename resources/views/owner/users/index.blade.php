@extends('layouts.owner')

@section('title', __('owner.usr.title'))

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-users"></i> {{ __('owner.usr.title') }}</h1>
        <p>{{ __('owner.usr.subtitle') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">{{ __('owner.overview') }}</a></li>
        <li class="breadcrumb-item">{{ __('owner.users_roles') }}</li>
    </ul>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-building fa-3x"></i>
            <div class="info">
                <h4>{{ __('owner.churches') }}</h4>
                <p><b>{{ number_format($overview['churches']) }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-user-secret fa-3x"></i>
            <div class="info">
                <h4>{{ __('owner.usr.church_staff') }}</h4>
                <p><b>{{ number_format($overview['staff_users']) }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-id-card fa-3x"></i>
            <div class="info">
                <h4>{{ __('owner.usr.registered_members') }}</h4>
                <p><b>{{ number_format($overview['active_members']) }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small danger coloured-icon">
            <i class="icon fa fa-shield fa-3x"></i>
            <div class="info">
                <h4>{{ __('owner.usr.platform_staff') }}</h4>
                <p><b>{{ number_format($overview['platform_staff']) }}</b></p>
            </div>
        </div>
    </div>
</div>

<div class="tile">
    <h3 class="tile-title">{{ __('owner.usr.by_church') }}</h3>
    <form method="GET" class="form-inline mb-3">
        <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="{{ __('owner.usr.search_church') }}"
            value="{{ $filters['search'] ?? '' }}">
        <select name="status" class="form-control mr-2 mb-2">
            <option value="">{{ __('pages.shared.all_statuses') }}</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> {{ __('common.filter') }}</button>
    </form>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>{{ __('owner.church_label') }}</th>
                    <th>{{ __('owner.status') }}</th>
                    <th class="text-center">{{ __('owner.usr.staff_users') }}</th>
                    <th class="text-center">{{ __('owner.usr.registered_members') }}</th>
                    <th class="text-right">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($churches as $church)
                    <tr>
                        <td>
                            <strong>{{ $church->name }}</strong>
                            @if($church->pastor_name)
                                <br><small class="text-muted">{{ $church->pastor_name }}</small>
                            @endif
                        </td>
                        <td>@include('owner.components.status-badge', ['status' => $church->status])</td>
                        <td class="text-center">
                            <span class="badge badge-primary">{{ number_format($church->staff_users_count) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-success">{{ number_format($church->active_members_count) }}</span>
                            @if($church->members_count > $church->active_members_count)
                                <br><small class="text-muted">{{ number_format($church->members_count) }} {{ __('owner.usr.total_suffix') }}</small>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('owner.churches.show', $church) }}" class="btn btn-sm btn-info" title="{{ __('owner.usr.view_church') }}">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">{{ __('owner.church.no_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $churches->links() }}
</div>

@if($platformStaff->isNotEmpty())
    <div class="tile">
        <h3 class="tile-title">{{ __('owner.usr.platform_staff_heading') }}</h3>
        <p class="text-muted mb-3">{{ __('owner.usr.platform_staff_desc') }}</p>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>{{ __('owner.name') }}</th>
                        <th>{{ __('owner.email') }}</th>
                        <th>{{ __('owner.type') }}</th>
                        <th>{{ __('owner.usr.roles') }}</th>
                        <th>{{ __('owner.status') }}</th>
                        <th>{{ __('owner.usr.last_login') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($platformStaff as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ ucfirst($user->user_type->value) }}</td>
                            <td>
                                @forelse($user->roles as $role)
                                    <span class="badge badge-primary">{{ $role->name }}</span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td>
                                <span class="badge badge-{{ $user->status->value === 'active' ? 'success' : 'danger' }}">
                                    {{ ucfirst($user->status->value) }}
                                </span>
                            </td>
                            <td>{{ $user->last_login_at?->format('M d, Y H:i') ?? __('common.never') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
