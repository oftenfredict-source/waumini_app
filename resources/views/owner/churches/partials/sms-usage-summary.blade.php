@php
    $smsMonth = now()->startOfMonth();
    $smsMonthInput = $smsMonth->format('Y-m');
@endphp
<div class="tile">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="tile-title mb-1">{{ __('owner.sms.title') }}</h3>
            <p class="text-muted mb-0">{{ __('owner.sms.church_summary_help', ['month' => $smsMonth->translatedFormat('F Y')]) }}</p>
        </div>
        <a href="{{ route('owner.sms-usage.show', ['church' => $church, 'month' => $smsMonthInput]) }}" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-list"></i> {{ __('owner.sms.view_details') }}
        </a>
    </div>

    <div class="row text-center">
        <div class="col-md-4">
            <h4 class="mb-0">{{ number_format($smsSummary['segments_used']) }}</h4>
            <small class="text-muted">{{ __('owner.sms.units_used') }}</small>
        </div>
        <div class="col-md-4">
            <h4 class="mb-0">{{ number_format($smsSummary['messages_count']) }}</h4>
            <small class="text-muted">{{ __('owner.sms.messages_sent') }}</small>
        </div>
        <div class="col-md-4">
            <h4 class="mb-0">
                @if($smsSummary['limit'])
                    {{ number_format($smsSummary['segments_used']) }} / {{ number_format($smsSummary['limit']) }}
                @else
                    {{ __('owner.set.unlimited') }}
                @endif
            </h4>
            <small class="text-muted">{{ __('owner.sms.monthly_limit') }}</small>
        </div>
    </div>

    @if($smsSummary['limit'] && $smsSummary['usage_percent'] !== null)
        <div class="progress mt-3" style="height: 8px;">
            <div class="progress-bar @if($smsSummary['usage_percent'] >= 90) bg-danger @elseif($smsSummary['usage_percent'] >= 75) bg-warning @else bg-success @endif"
                role="progressbar"
                style="width: {{ $smsSummary['usage_percent'] }}%"
                aria-valuenow="{{ $smsSummary['usage_percent'] }}"
                aria-valuemin="0"
                aria-valuemax="100"></div>
        </div>
        <p class="small text-muted mb-0 mt-2">{{ __('owner.sms.usage_percent', ['percent' => $smsSummary['usage_percent']]) }}</p>
    @endif

    <p class="small text-muted mb-0 mt-3">{{ __('owner.sms.segment_rule') }}</p>
</div>
