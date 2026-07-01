@extends('layouts.owner')

@section('title', __('owner.sms.title'))

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-commenting"></i> {{ __('owner.sms.title') }}</h1>
        <p>{{ __('owner.sms.subtitle') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">{{ __('owner.overview') }}</a></li>
        <li class="breadcrumb-item">{{ __('owner.sms_usage') }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-building fa-3x"></i>
            <div class="info">
                <h4>{{ __('owner.sms.churches_using_sms') }}</h4>
                <p><b>{{ $totals['churches_count'] }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-comment fa-3x"></i>
            <div class="info">
                <h4>{{ __('owner.sms.total_messages') }}</h4>
                <p><b>{{ number_format($totals['messages_count']) }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-calculator fa-3x"></i>
            <div class="info">
                <h4>{{ __('owner.sms.total_units') }}</h4>
                <p><b>{{ number_format($totals['segments_used']) }}</b></p>
            </div>
        </div>
    </div>
</div>

<div class="tile">
    <form method="GET" class="form-inline mb-3">
        <label class="mr-2 mb-2" for="month">{{ __('owner.sms.month') }}</label>
        <input type="month" id="month" name="month" class="form-control mr-2 mb-2" value="{{ $monthInput }}">
        <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-filter"></i> {{ __('common.filter') }}</button>
    </form>

    <p class="text-muted">{{ __('owner.sms.period_label', ['month' => $month->translatedFormat('F Y')]) }}</p>
    <p class="small text-muted">{{ __('owner.sms.segment_rule') }}</p>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>{{ __('owner.church_label') }}</th>
                    <th>{{ __('owner.package') }}</th>
                    <th class="text-right">{{ __('owner.sms.messages_sent') }}</th>
                    <th class="text-right">{{ __('owner.sms.units_used') }}</th>
                    <th class="text-right">{{ __('owner.sms.monthly_limit') }}</th>
                    <th>{{ __('owner.sms.usage') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($churches as $row)
                    @php($church = $row['church'])
                    <tr>
                        <td>
                            <a href="{{ route('owner.churches.show', $church) }}">{{ $church->name }}</a>
                        </td>
                        <td>{{ $church->activeSubscription?->package?->name ?? '—' }}</td>
                        <td class="text-right">{{ number_format($row['messages_count']) }}</td>
                        <td class="text-right">{{ number_format($row['segments_used']) }}</td>
                        <td class="text-right">
                            @if($row['limit'])
                                {{ number_format($row['limit']) }}
                            @else
                                {{ __('owner.set.unlimited') }}
                            @endif
                        </td>
                        <td style="min-width: 140px;">
                            @if($row['limit'] && $row['usage_percent'] !== null)
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar @if($row['usage_percent'] >= 90) bg-danger @elseif($row['usage_percent'] >= 75) bg-warning @else bg-success @endif"
                                        style="width: {{ $row['usage_percent'] }}%"></div>
                                </div>
                                <small class="text-muted">{{ $row['usage_percent'] }}%</small>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('owner.sms-usage.show', ['church' => $church, 'month' => $monthInput]) }}" class="btn btn-sm btn-outline-primary">
                                {{ __('owner.sms.view_details') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">{{ __('owner.sms.no_churches') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
