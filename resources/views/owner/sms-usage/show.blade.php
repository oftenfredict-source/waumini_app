@extends('layouts.owner')

@section('title', __('owner.sms.church_title', ['church' => $church->name]))

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">{{ __('owner.sms.church_title', ['church' => $church->name]) }}</h1>
        <p class="text-muted mb-0">{{ __('owner.sms.church_subtitle') }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('owner.sms-usage.index', ['month' => $monthInput]) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> {{ __('owner.sms_usage') }}
        </a>
        <a href="{{ route('owner.churches.show', $church) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-building me-1"></i> {{ __('owner.sms.back_to_church') }}
        </a>
    </div>
</div>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div class="alert border-0 mb-0 flex-grow-1" style="background:rgba(148,0,0,.06);color:#4a1a1a;">
        <div class="fw-semibold" style="color:#940000;">
            {{ __('owner.sms.period_label', ['month' => $month->translatedFormat('F Y')]) }}
        </div>
        <div class="small mb-0">{{ __('owner.sms.segment_rule') }}</div>
    </div>
    <form method="GET" class="d-flex flex-wrap align-items-center gap-2">
        <label class="small text-muted mb-0" for="month">{{ __('owner.sms.month') }}</label>
        <input type="month" id="month" name="month" class="form-control form-control-sm" style="width:auto;" value="{{ $monthInput }}">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-funnel me-1"></i> {{ __('common.filter') }}
        </button>
    </form>
</div>

<div class="row g-3 g-lg-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(8,145,178,.12);color:#0891b2;">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.sms.messages_sent') }}</p>
                        <div class="h3 mb-0">{{ number_format($summary['messages_count']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(22,163,74,.12);color:#16a34a;">
                        <i class="bi bi-calculator"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.sms.units_used') }}</p>
                        <div class="h3 mb-0">{{ number_format($summary['segments_used']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-sliders"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.sms.monthly_limit') }}</p>
                        <div class="h3 mb-0">
                            @if($summary['limit'])
                                {{ number_format($summary['limit']) }}
                            @else
                                ∞
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    @php
                        $usage = $summary['usage_percent'];
                        $usageColor = $usage === null ? '#52525b' : ($usage >= 90 ? '#dc2626' : ($usage >= 75 ? '#d97706' : '#16a34a'));
                    @endphp
                    <div class="stats-icon" style="background:{{ $usageColor }}1f;color:{{ $usageColor }};">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.sms.usage') }}</p>
                        <div class="h3 mb-0">{{ $usage !== null ? $usage.'%' : '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($summary['limit'] && $summary['usage_percent'] !== null)
    <div class="card owner-panel-card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold">{{ __('owner.sms.usage_percent', ['percent' => $summary['usage_percent']]) }}</span>
                <span class="text-muted small">
                    {{ number_format($summary['segments_used']) }} / {{ number_format($summary['limit']) }}
                </span>
            </div>
            <div class="progress" style="height:10px;background:#f4f4f5;">
                <div class="progress-bar" role="progressbar"
                     style="width:{{ min(100, $summary['usage_percent']) }}%;background:{{ $usageColor }};"
                     aria-valuenow="{{ $summary['usage_percent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>
@endif

<div class="card owner-panel-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ __('owner.sms.messages_sent') }}</span>
        <span class="badge text-bg-light border">{{ $messages->total() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('common.date') }}</th>
                        <th>{{ __('pages.system_sms.recipient') }}</th>
                        <th>{{ __('pages.system_sms.template') }}</th>
                        <th class="text-end">{{ __('owner.sms.units') }}</th>
                        <th class="text-end">{{ __('owner.sms.characters') }}</th>
                        <th>{{ __('pages.system_sms.message_col') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $smsLog)
                        <tr>
                            <td class="text-nowrap">{{ $smsLog->created_at?->format('M d, Y H:i') }}</td>
                            <td>{{ $smsLog->recipient }}</td>
                            <td>
                                <span class="badge text-bg-light border">{{ $smsLog->contextLabel() }}</span>
                            </td>
                            <td class="text-end fw-semibold">{{ $smsLog->segments }}</td>
                            <td class="text-end">{{ mb_strlen((string) $smsLog->message) }}</td>
                            <td class="text-muted">{{ \Illuminate\Support\Str::limit($smsLog->message, 80) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">{{ __('owner.sms.no_messages') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($messages->hasPages())
        <div class="card-footer bg-white">
            {{ $messages->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
