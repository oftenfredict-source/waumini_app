@extends('layouts.church')

@section('title', 'Member Dashboard')

@push('styles')
<style>
    .member-hero {
        background: linear-gradient(135deg, #940000 0%, #600000 100%);
        border-radius: 8px;
        color: #fff;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .member-hero h1 { color: #fff; font-size: 1.45rem; margin-bottom: 0.25rem; }
    .member-hero p { color: rgba(255,255,255,.88); margin: 0; }
    .member-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(255,255,255,.35);
    }
    .member-avatar-placeholder {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: rgba(255,255,255,.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: #fff;
        border: 3px solid rgba(255,255,255,.35);
    }
    .member-section-title {
        color: #940000;
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 1rem;
    }
    .member-list-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .member-list-item:last-child { border-bottom: none; }
</style>
@endpush

@section('content')
@php
    $data = $dashboard;
    $currency = $church->currency ?? 'TZS';
@endphp

<div class="member-hero d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-3">
        @if($member->profilePictureUrl())
            <img src="{{ $member->profilePictureUrl() }}" alt="{{ $member->full_name }}" class="member-avatar">
        @else
            <div class="member-avatar-placeholder"><i class="fa fa-user"></i></div>
        @endif
        <div>
            <h1>Welcome, {{ $member->full_name }}</h1>
            <p>{{ $church->name }} — Member ID <code style="color:#fff;">{{ $member->member_number }}</code></p>
        </div>
    </div>
    <a href="{{ route('church.member.profile.edit') }}" class="btn btn-light btn-sm mr-2">
        <i class="fa fa-pencil"></i> Edit Profile
    </a>
    <a href="{{ route('church.member.requests.create') }}" class="btn btn-light btn-sm">
        <i class="fa fa-envelope"></i> New Request
    </a>
</div>

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="tile p-3">
            <div class="text-muted small">Membership</div>
            <h4 class="mb-0">{{ ucfirst($member->membership_type->value) }}</h4>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="tile p-3">
            <div class="text-muted small">Status</div>
            <h4 class="mb-0">{{ ucfirst($member->status->value) }}</h4>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="tile p-3">
            <div class="text-muted small">Tithes ({{ now()->year }})</div>
            <h4 class="mb-0">{{ $currency }} {{ number_format($data['giving']['tithes_year'], 0) }}</h4>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="tile p-3">
            <div class="text-muted small">Offerings ({{ now()->year }})</div>
            <h4 class="mb-0">{{ $currency }} {{ number_format($data['giving']['offerings_year'], 0) }}</h4>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="tile p-3">
            <div class="text-muted small">Open Requests</div>
            <h4 class="mb-0">{{ $data['open_requests'] }}</h4>
        </div>
    </div>
</div>

@if($data['recent_requests']->isNotEmpty())
<div class="tile mb-3">
    <h3 class="member-section-title"><i class="fa fa-envelope"></i> Recent Requests</h3>
    @foreach($data['recent_requests'] as $item)
        <div class="member-list-item d-flex justify-content-between align-items-center">
            <div>
                <strong><a href="{{ route('church.member.requests.show', $item) }}">{{ $item->subject }}</a></strong>
                <div class="text-muted small">{{ $item->type->label() }} → {{ $item->assignedLeader?->positionLabel() }}</div>
            </div>
            <span class="badge badge-{{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span>
        </div>
    @endforeach
    <a href="{{ route('church.member.requests.index') }}" class="btn btn-sm btn-link px-0 mt-2">View all requests</a>
</div>
@endif

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="tile h-100">
            <h3 class="member-section-title"><i class="fa fa-bullhorn"></i> Announcements</h3>
            @forelse($data['announcements'] as $announcement)
                <div class="member-list-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <strong>
                            @if($announcement->is_pinned)<i class="fa fa-thumb-tack text-warning"></i> @endif
                            <a href="{{ route('church.member.announcements.show', $announcement) }}">{{ $announcement->title }}</a>
                        </strong>
                        <small class="text-muted">{{ $announcement->created_at?->diffForHumans() }}</small>
                    </div>
                    <div class="text-muted small mt-1">{{ Str::limit(strip_tags($announcement->content), 90) }}</div>
                </div>
            @empty
                <p class="text-muted mb-0">No announcements for you right now.</p>
            @endforelse
            <a href="{{ route('church.member.announcements.index') }}" class="btn btn-sm btn-link px-0 mt-2">View all</a>
        </div>
    </div>

    <div class="col-lg-6 mb-3">
        <div class="tile h-100">
            <h3 class="member-section-title"><i class="fa fa-calendar"></i> Upcoming Services</h3>
            @forelse($data['upcoming_services'] as $service)
                <div class="member-list-item d-flex justify-content-between">
                    <div>
                        <strong>{{ $service->displayTitle() }}</strong>
                        @if($service->preacher)<div class="text-muted small">{{ $service->preacher }}</div>@endif
                    </div>
                    <span class="badge badge-primary">{{ $service->service_date?->format('M d') }}</span>
                </div>
            @empty
                <p class="text-muted mb-0">No upcoming services scheduled.</p>
            @endforelse
            <a href="{{ route('church.member.services.index') }}" class="btn btn-sm btn-link px-0 mt-2">View all services</a>
        </div>
    </div>
</div>

<div class="tile">
    <h3 class="member-section-title"><i class="fa fa-star"></i> Church Leadership</h3>
    <div class="row">
        @forelse($data['leaders'] as $leader)
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="border rounded p-3 h-100">
                    <div class="font-weight-bold">{{ $leader->member?->full_name ?? '—' }}</div>
                    <div class="text-muted small">{{ $leader->positionLabel() }}</div>
                </div>
            </div>
        @empty
            <div class="col-12"><p class="text-muted mb-0">No active leaders listed.</p></div>
        @endforelse
    </div>
    <a href="{{ route('church.member.leaders.index') }}" class="btn btn-sm btn-link px-0">View all leaders</a>
</div>
@endsection
