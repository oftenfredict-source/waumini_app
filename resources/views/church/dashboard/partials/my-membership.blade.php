@php
    $member = $dashboard['member_portal']['member'] ?? $user->member;
    $personal = $dashboard['member_portal'];
    $currency = $dashboard['currency'];
@endphp

<div class="tile mb-3 border-left border-primary" style="border-left-width: 4px !important;">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            @if($member?->profilePictureUrl())
                <img src="{{ $member->profilePictureUrl() }}" alt="{{ $member->full_name }}"
                     style="width:56px;height:56px;border-radius:50%;object-fit:cover;">
            @else
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px;">
                    <i class="fa fa-user text-muted"></i>
                </div>
            @endif
            <div>
                <h3 class="mb-1" style="font-size:1.1rem;">
                    <i class="fa fa-id-card text-primary"></i> My Membership
                </h3>
                <div class="font-weight-bold">{{ $member?->full_name }}</div>
                <small class="text-muted">
                    Member ID <code>{{ $member?->member_number }}</code>
                    · {{ ucfirst($member?->membership_type->value ?? 'member') }}
                    · {{ ucfirst($member?->status->value ?? 'active') }}
                </small>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('church.member.profile.edit') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-user"></i> My Profile
            </a>
            <a href="{{ route('church.member.requests.create') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa fa-envelope"></i> New Request
            </a>
        </div>
    </div>

    @if($personal)
        <hr class="my-3">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-2">
                <div class="text-muted small">Tithes ({{ now()->year }})</div>
                <strong>{{ $currency }} {{ number_format($personal['giving']['tithes_year'], 0) }}</strong>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="text-muted small">Offerings ({{ now()->year }})</div>
                <strong>{{ $currency }} {{ number_format($personal['giving']['offerings_year'], 0) }}</strong>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="text-muted small">Open Requests</div>
                <strong>{{ $personal['open_requests'] }}</strong>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="text-muted small">Phone</div>
                <strong>{{ $member?->phone_number ?? '—' }}</strong>
            </div>
        </div>
    @endif
</div>
