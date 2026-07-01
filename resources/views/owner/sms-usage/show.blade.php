@extends('layouts.owner')

@section('title', __('owner.sms.church_title', ['church' => $church->name]))

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-commenting"></i> {{ __('owner.sms.church_title', ['church' => $church->name]) }}</h1>
        <p>{{ __('owner.sms.church_subtitle') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">{{ __('owner.overview') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('owner.sms-usage.index') }}">{{ __('owner.sms_usage') }}</a></li>
        <li class="breadcrumb-item">{{ $church->name }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-comment fa-3x"></i>
            <div class="info">
                <h4>{{ __('owner.sms.messages_sent') }}</h4>
                <p><b>{{ number_format($summary['messages_count']) }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-calculator fa-3x"></i>
            <div class="info">
                <h4>{{ __('owner.sms.units_used') }}</h4>
                <p><b>{{ number_format($summary['segments_used']) }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-sliders fa-3x"></i>
            <div class="info">
                <h4>{{ __('owner.sms.monthly_limit') }}</h4>
                <p><b>
                    @if($summary['limit'])
                        {{ number_format($summary['limit']) }}
                    @else
                        {{ __('owner.set.unlimited') }}
                    @endif
                </b></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-percent fa-3x"></i>
            <div class="info">
                <h4>{{ __('owner.sms.usage') }}</h4>
                <p><b>{{ $summary['usage_percent'] !== null ? $summary['usage_percent'].'%' : '—' }}</b></p>
            </div>
        </div>
    </div>
</div>

<div class="tile">
    <form method="GET" class="form-inline mb-3">
        <label class="mr-2 mb-2" for="month">{{ __('owner.sms.month') }}</label>
        <input type="month" id="month" name="month" class="form-control mr-2 mb-2" value="{{ $monthInput }}">
        <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-filter"></i> {{ __('common.filter') }}</button>
        <a href="{{ route('owner.churches.show', $church) }}" class="btn btn-outline-secondary mb-2 ml-2">
            <i class="fa fa-building"></i> {{ __('owner.sms.back_to_church') }}
        </a>
    </form>

    <p class="text-muted">{{ __('owner.sms.period_label', ['month' => $month->translatedFormat('F Y')]) }}</p>
    <p class="small text-muted">{{ __('owner.sms.segment_rule') }}</p>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>{{ __('common.date') }}</th>
                    <th>{{ __('pages.system_sms.recipient') }}</th>
                    <th>{{ __('pages.system_sms.template') }}</th>
                    <th class="text-right">{{ __('owner.sms.units') }}</th>
                    <th class="text-right">{{ __('owner.sms.characters') }}</th>
                    <th>{{ __('pages.system_sms.message_col') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $smsLog)
                    <tr>
                        <td class="text-nowrap">{{ $smsLog->created_at?->format('M d, Y H:i') }}</td>
                        <td>{{ $smsLog->recipient }}</td>
                        <td>{{ $smsLog->contextLabel() }}</td>
                        <td class="text-right">{{ $smsLog->segments }}</td>
                        <td class="text-right">{{ mb_strlen((string) $smsLog->message) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($smsLog->message, 80) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">{{ __('owner.sms.no_messages') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $messages->links() }}
</div>
@endsection
