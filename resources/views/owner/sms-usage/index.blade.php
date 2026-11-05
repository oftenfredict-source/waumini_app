@extends('layouts.owner')

@section('title', __('owner.sms.title'))

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">{{ __('owner.sms.title') }}</h1>
        <p class="text-muted mb-0">{{ __('owner.sms.subtitle') }}</p>
    </div>
    <form method="GET" class="d-flex flex-wrap align-items-center gap-2">
        <label class="small text-muted mb-0" for="month">{{ __('owner.sms.month') }}</label>
        <input type="month" id="month" name="month" class="form-control form-control-sm" style="width:auto;" value="{{ $monthInput }}">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-funnel me-1"></i> {{ __('common.filter') }}
        </button>
    </form>
</div>

<div class="alert border-0 mb-4" style="background:rgba(148,0,0,.06);color:#4a1a1a;">
    <div class="fw-semibold mb-1" style="color:#940000;">
        {{ __('owner.sms.period_label', ['month' => $month->translatedFormat('F Y')]) }}
    </div>
    <div class="small mb-0">{{ __('owner.sms.segment_rule') }}</div>
</div>

<div class="row g-3 g-lg-4 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.sms.churches_using_sms') }}</p>
                        <div class="h3 mb-0">{{ number_format($totals['churches_count']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(8,145,178,.12);color:#0891b2;">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.sms.total_messages') }}</p>
                        <div class="h3 mb-0">{{ number_format($totals['messages_count']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(22,163,74,.12);color:#16a34a;">
                        <i class="bi bi-calculator"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.sms.total_units') }}</p>
                        <div class="h3 mb-0">{{ number_format($totals['segments_used']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card owner-panel-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ __('owner.sms_usage') }}</span>
        <span class="badge text-bg-light border">{{ count($churches) }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('owner.church_label') }}</th>
                        <th>{{ __('owner.package') }}</th>
                        <th class="text-end">{{ __('owner.sms.messages_sent') }}</th>
                        <th class="text-end">{{ __('owner.sms.units_used') }}</th>
                        <th class="text-end">{{ __('owner.sms.monthly_limit') }}</th>
                        <th style="min-width:160px;">{{ __('owner.sms.usage') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($churches as $row)
                        @php
                            $church = $row['church'];
                            $barColor = null;
                            if ($row['limit'] && $row['usage_percent'] !== null) {
                                $barColor = $row['usage_percent'] >= 90
                                    ? '#dc2626'
                                    : ($row['usage_percent'] >= 75 ? '#d97706' : '#16a34a');
                            }
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('owner.churches.show', $church) }}" class="fw-semibold text-decoration-none" style="color:#940000;">
                                    {{ $church->name }}
                                </a>
                            </td>
                            <td>{{ $church->activeSubscription?->package?->name ?? '—' }}</td>
                            <td class="text-end">{{ number_format($row['messages_count']) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($row['segments_used']) }}</td>
                            <td class="text-end">
                                @if($row['limit'])
                                    {{ number_format($row['limit']) }}
                                @else
                                    <span class="badge rounded-pill" style="background:rgba(148,0,0,.1);color:#940000;">{{ __('owner.set.unlimited') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($barColor !== null)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:8px;background:#f4f4f5;">
                                            <div class="progress-bar" role="progressbar"
                                                 style="width: {{ min(100, $row['usage_percent']) }}%;background:{{ $barColor }};"
                                                 aria-valuenow="{{ $row['usage_percent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <small class="text-muted" style="min-width:2.5rem;">{{ $row['usage_percent'] }}%</small>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('owner.sms-usage.show', ['church' => $church, 'month' => $monthInput]) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    {{ __('owner.sms.view_details') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">{{ __('owner.sms.no_churches') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
