@extends('layouts.church')

@section('title', 'OTP Management')

@push('styles')
<style>
    .otp-code {
        font-family: 'Courier New', monospace;
        font-weight: bold;
        font-size: 1.1rem;
        letter-spacing: 0.2em;
        color: #2c3e50;
    }

    .otp-code-active {
        color: #28a745;
    }
</style>
@endpush

@section('content')
@include('church.system.partials.nav')

<div class="app-title">
    <div>
        <h1><i class="fa fa-key"></i> OTP Management</h1>
        <p>View login verification codes issued for this church</p>
    </div>
</div>

<div class="tile mb-3">
    @if($enabled)
        <div class="alert alert-success mb-0">
            <i class="fa fa-check-circle"></i>
            <strong>SMS OTP login is enabled</strong> for this church.
            Active codes appear below and can be shared with users who did not receive the SMS.
            Manage this under <a href="{{ route('church.system.settings.index', ['tab' => 'security']) }}">Settings → Security</a>.
        </div>
    @else
        <div class="alert alert-info mb-0">
            <i class="fa fa-info-circle"></i>
            SMS OTP login is currently <strong>disabled</strong>.
            Enable it under <a href="{{ route('church.system.settings.index', ['tab' => 'security']) }}">Settings → Security</a>
            after platform SMS and your church SMS package are active.
        </div>
    @endif
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <div class="tile text-center">
            <h4 class="mb-0">{{ $stats['total'] }}</h4>
            <small class="text-muted">Total OTPs</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="tile text-center">
            <h4 class="mb-0 text-success">{{ $stats['active'] }}</h4>
            <small class="text-muted">Active Now</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="tile text-center">
            <h4 class="mb-0">{{ $stats['used'] }}</h4>
            <small class="text-muted">Used</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="tile text-center">
            <h4 class="mb-0">{{ $stats['today'] }}</h4>
            <small class="text-muted">Today</small>
        </div>
    </div>
</div>

<div class="tile">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h4 class="mb-0">Recent OTP Activity</h4>
        <form method="GET" action="{{ route('church.system.otps.index') }}" class="form-inline d-flex flex-wrap gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search code, user, phone…"
                   value="{{ request('search') }}">
            <select name="status" class="form-control form-control-sm">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="used" @selected(request('status') === 'used')>Used</option>
                <option value="expired" @selected(request('status') === 'expired')>Expired</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Filter</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('church.system.otps.index') }}" class="btn btn-secondary btn-sm">Clear</a>
            @endif
        </form>
    </div>

    @if($otps->isEmpty())
        <p class="text-muted mb-0">No OTP codes have been issued yet.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>OTP Code</th>
                        <th>User</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Attempts</th>
                        <th>Expires</th>
                        <th>Sent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($otps as $otp)
                        @php
                            $isActive = ! $otp->is_used && ! $otp->isExpired();
                        @endphp
                        <tr>
                            <td>
                                <span class="otp-code {{ $isActive ? 'otp-code-active' : '' }}">{{ $otp->otp_code }}</span>
                                @if($isActive)
                                    <button type="button" class="btn btn-link btn-sm p-0 ml-2 copy-otp"
                                            data-code="{{ $otp->otp_code }}" title="Copy code">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                @endif
                            </td>
                            <td>
                                {{ $otp->user?->name ?? '—' }}
                                <br><small class="text-muted">{{ $otp->login_identifier }}</small>
                            </td>
                            <td>{{ $otp->phone ?? '—' }}</td>
                            <td>
                                @if($otp->is_used)
                                    <span class="badge badge-secondary">Used</span>
                                @elseif($otp->isExpired())
                                    <span class="badge badge-warning">Expired</span>
                                @else
                                    <span class="badge badge-success">Active</span>
                                @endif
                            </td>
                            <td>{{ $otp->attempts }}/5</td>
                            <td>
                                {{ $otp->expires_at?->format('M d, H:i') ?? '—' }}
                                @if($otp->expires_at && ! $otp->is_used)
                                    <br><small class="text-muted">{{ $otp->expires_at->diffForHumans() }}</small>
                                @endif
                            </td>
                            <td>{{ $otp->created_at?->format('M d, H:i') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $otps->links() }}
    @endif
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.copy-otp').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var code = btn.getAttribute('data-code');
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(code);
        } else {
            var input = document.createElement('input');
            input.value = code;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
        }
        btn.innerHTML = '<i class="fa fa-check text-success"></i>';
        setTimeout(function () {
            btn.innerHTML = '<i class="fa fa-copy"></i>';
        }, 1500);
    });
});
</script>
@endpush
