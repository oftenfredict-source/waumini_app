@extends('layouts.owner')

@section('title', __('owner.pay.title'))

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-money"></i> {{ __('owner.pay.title') }}</h1>
        <p>{{ __('owner.pay.subtitle') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">{{ __('owner.overview') }}</a></li>
        <li class="breadcrumb-item">{{ __('owner.payments') }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-list fa-3x"></i>
            <div class="info"><h4>{{ __('owner.pay.total') }}</h4><p><b>{{ $stats['total'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-check fa-3x"></i>
            <div class="info"><h4>{{ __('owner.pay.completed') }}</h4><p><b>{{ $stats['completed'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-clock-o fa-3x"></i>
            <div class="info"><h4>{{ __('owner.pay.pending') }}</h4><p><b>{{ $stats['pending'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-dollar fa-3x"></i>
            <div class="info"><h4>{{ __('owner.pay.collected') }}</h4><p><b>${{ number_format($stats['revenue'], 2) }}</b></p></div>
        </div>
    </div>
</div>

<div class="tile">
    <form method="GET" class="form-inline mb-3">
        <select name="status" class="form-control mr-2">
            <option value="">{{ __('pages.shared.all_statuses') }}</option>
            <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>{{ __('owner.pay.pending') }}</option>
            <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>{{ __('owner.pay.completed') }}</option>
            <option value="failed" @selected(($filters['status'] ?? '') === 'failed')>{{ __('pages.system_sms.failed') }}</option>
        </select>
        <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> {{ __('common.filter') }}</button>
    </form>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>{{ __('owner.church_label') }}</th>
                    <th>{{ __('common.amount') }}</th>
                    <th>{{ __('owner.pay.method') }}</th>
                    <th>{{ __('owner.status') }}</th>
                    <th>{{ __('owner.pay.paid_at') }}</th>
                    <th>{{ __('common.reference') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->church?->name }}</td>
                        <td>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                        <td>{{ ucfirst($payment->method) }}</td>
                        <td>
                            <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td>{{ $payment->paid_at?->format('M d, Y H:i') ?? '—' }}</td>
                        <td>{{ $payment->provider_reference ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x d-block mb-2"></i>
                            {{ __('owner.pay.no_payments') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $payments->links() }}
</div>
@endsection
