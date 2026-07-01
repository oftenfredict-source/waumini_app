@extends('layouts.church')

@section('title', __('pages.system_otps.title'))

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

@include('partials.page-header', [
    'icon' => 'fa fa-key',
    'title' => __('pages.system_otps.title'),
    'subtitle' => __('pages.system_otps.subtitle'),
])

<div class="tile mb-3">
    @if($enabled)
        <div class="alert alert-success mb-0">
            <i class="fa fa-check-circle"></i>
            <strong>{{ __('pages.system_otps.enabled_alert') }}</strong>
            {{ __('pages.system_otps.enabled_detail') }}
            <a href="{{ route('church.system.settings.index', ['tab' => 'security']) }}">{{ __('pages.system_otps.enabled_settings') }}</a>
        </div>
    @else
        <div class="alert alert-info mb-0">
            <i class="fa fa-info-circle"></i>
            {{ __('pages.system_otps.disabled_alert') }}
            {{ __('pages.system_otps.disabled_detail') }}
            <a href="{{ route('church.system.settings.index', ['tab' => 'security']) }}">{{ __('pages.system_otps.enabled_settings') }}</a>
        </div>
    @endif
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <div class="tile text-center">
            <h4 class="mb-0">{{ $stats['total'] }}</h4>
            <small class="text-muted">{{ __('pages.system_otps.total_otps') }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="tile text-center">
            <h4 class="mb-0 text-success">{{ $stats['active'] }}</h4>
            <small class="text-muted">{{ __('pages.system_otps.active_now') }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="tile text-center">
            <h4 class="mb-0">{{ $stats['used'] }}</h4>
            <small class="text-muted">{{ __('common.used') }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="tile text-center">
            <h4 class="mb-0">{{ $stats['today'] }}</h4>
            <small class="text-muted">{{ __('common.today') }}</small>
        </div>
    </div>
</div>

<div class="tile">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h4 class="mb-0">{{ __('pages.system_otps.recent_activity') }}</h4>
        <form method="GET" action="{{ route('church.system.otps.index') }}" class="form-inline d-flex flex-wrap gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="{{ __('pages.system_otps.search_placeholder') }}"
                   value="{{ request('search') }}">
            <select name="status" class="form-control form-control-sm">
                <option value="">{{ __('pages.shared.all_statuses') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('common.active') }}</option>
                <option value="used" @selected(request('status') === 'used')>{{ __('common.used') }}</option>
                <option value="expired" @selected(request('status') === 'expired')>{{ __('common.expired') }}</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> {{ __('common.filter') }}</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('church.system.otps.index') }}" class="btn btn-secondary btn-sm">{{ __('common.clear') }}</a>
            @endif
        </form>
    </div>

    @if($otps->isEmpty())
        <p class="text-muted mb-0">{{ __('pages.system_otps.empty') }}</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>{{ __('pages.system_otps.otp_code') }}</th>
                        <th>{{ __('common.name') }}</th>
                        <th>{{ __('common.phone') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th>{{ __('pages.system_otps.attempts') }}</th>
                        <th>{{ __('pages.system_otps.expires') }}</th>
                        <th>{{ __('pages.system_otps.sent') }}</th>
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
                                            data-code="{{ $otp->otp_code }}" title="{{ __('pages.system_otps.copy') }}">
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
                                    <span class="badge badge-secondary">{{ __('common.used') }}</span>
                                @elseif($otp->isExpired())
                                    <span class="badge badge-warning">{{ __('common.expired') }}</span>
                                @else
                                    <span class="badge badge-success">{{ __('common.active') }}</span>
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
