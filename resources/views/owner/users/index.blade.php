@extends('layouts.owner')

@section('title', __('owner.usr.title'))

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">{{ __('owner.usr.title') }}</h1>
        <p class="text-muted mb-0">{{ __('owner.usr.subtitle') }}</p>
    </div>
</div>

<div class="row g-3 g-lg-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.churches') }}</p>
                        <div class="h3 mb-0">{{ number_format($overview['churches']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(8,145,178,.12);color:#0891b2;">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.usr.church_staff') }}</p>
                        <div class="h3 mb-0">{{ number_format($overview['staff_users']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(217,119,6,.12);color:#d97706;">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.usr.registered_members') }}</p>
                        <div class="h3 mb-0">{{ number_format($overview['active_members']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(148,0,0,.1);color:#940000;">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.usr.platform_staff') }}</p>
                        <div class="h3 mb-0">{{ number_format($overview['platform_staff']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card owner-panel-card mb-4">
    <div class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-stretch align-items-lg-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <span>{{ __('owner.usr.by_church') }}</span>
            <span class="badge text-bg-light border">{{ $churches->total() }}</span>
        </div>
        <form method="GET" class="d-flex align-items-center flex-nowrap gap-2">
            <input type="text" name="search" class="form-control form-control-sm" style="min-width:10rem;max-width:16rem;"
                   placeholder="{{ __('owner.usr.search_church') }}" value="{{ $filters['search'] ?? '' }}">
            <select name="status" class="form-select form-select-sm" style="width:auto;min-width:8rem;">
                <option value="">{{ __('pages.shared.all_statuses') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm text-nowrap">
                <i class="bi bi-funnel me-1"></i> {{ __('common.filter') }}
            </button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('owner.church_label') }}</th>
                        <th>{{ __('owner.status') }}</th>
                        <th class="text-center">{{ __('owner.usr.staff_users') }}</th>
                        <th class="text-center">{{ __('owner.usr.registered_members') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($churches as $church)
                        <tr>
                            <td>
                                <a href="{{ route('owner.churches.show', $church) }}" class="fw-semibold text-decoration-none" style="color:#940000;">
                                    {{ $church->name }}
                                </a>
                                @if($church->pastor_name)
                                    <div class="small text-muted">{{ $church->pastor_name }}</div>
                                @endif
                            </td>
                            <td>@include('owner.components.status-badge', ['status' => $church->status])</td>
                            <td class="text-center">
                                <span class="badge rounded-pill" style="background:rgba(148,0,0,.1);color:#940000;">
                                    {{ number_format($church->staff_users_count) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill" style="background:rgba(22,163,74,.12);color:#16a34a;">
                                    {{ number_format($church->active_members_count) }}
                                </span>
                                @if($church->members_count > $church->active_members_count)
                                    <div class="small text-muted">{{ number_format($church->members_count) }} {{ __('owner.usr.total_suffix') }}</div>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('owner.churches.show', $church) }}" class="btn btn-sm btn-outline-primary" title="{{ __('owner.usr.view_church') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-inbox d-block mb-2" style="font-size:1.75rem;"></i>
                                {{ __('owner.church.no_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($churches->hasPages())
        <div class="card-footer bg-white">
            {{ $churches->links() }}
        </div>
    @endif
</div>

@if($platformStaff->isNotEmpty())
    <div class="card owner-panel-card">
        <div class="card-header">
            <div class="fw-semibold">{{ __('owner.usr.platform_staff_heading') }}</div>
            <div class="small text-muted mt-1">{{ __('owner.usr.platform_staff_desc') }}</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
                                <td class="fw-semibold">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ ucfirst($user->user_type->value) }}</td>
                                <td>
                                    @forelse($user->roles as $role)
                                        <span class="badge rounded-pill me-1" style="background:rgba(148,0,0,.1);color:#940000;">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if($user->status->value === 'active')
                                        <span class="badge rounded-pill" style="background:rgba(22,163,74,.12);color:#16a34a;">{{ ucfirst($user->status->value) }}</span>
                                    @else
                                        <span class="badge rounded-pill" style="background:rgba(220,38,38,.12);color:#dc2626;">{{ ucfirst($user->status->value) }}</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $user->last_login_at?->format('M d, Y H:i') ?? __('common.never') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
