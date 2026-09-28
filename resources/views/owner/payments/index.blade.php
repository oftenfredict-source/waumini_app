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
            <div class="info"><h4>{{ __('owner.pay.collected') }}</h4><p><b>{{ \App\Models\SystemSetting::platformCurrency() }} {{ number_format($stats['revenue'], 0) }}</b></p></div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="alert alert-warning mb-0 d-flex justify-content-between align-items-center flex-wrap">
            <span>
                <i class="fa fa-exclamation-triangle"></i>
                {{ __('owner.inv.outstanding') }}:
                <strong>{{ $stats['outstanding_count'] }}</strong>
                ({{ \App\Models\SystemSetting::platformCurrency() }} {{ number_format($stats['outstanding'], 0) }})
            </span>
            <a href="{{ route('owner.invoices.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-dark mt-2 mt-md-0">
                {{ __('owner.inv.view_debts') }}
            </a>
        </div>
    </div>
</div>

<div class="tile">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <form method="GET" class="form-inline">
            <select name="church_id" class="form-control mr-2 mb-2">
                <option value="">{{ __('owner.inv.all_churches') }}</option>
                @foreach($churches as $church)
                    <option value="{{ $church->id }}" @selected(($filters['church_id'] ?? '') == $church->id)>{{ $church->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">{{ __('pages.shared.all_statuses') }}</option>
                <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>{{ __('owner.pay.pending') }}</option>
                <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>{{ __('owner.pay.completed') }}</option>
                <option value="failed" @selected(($filters['status'] ?? '') === 'failed')>{{ __('pages.system_sms.failed') }}</option>
            </select>
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-filter"></i> {{ __('common.filter') }}</button>
        </form>

        @can('create', \App\Models\Invoice::class)
            <button type="button" class="btn btn-success mb-2" data-toggle="modal" data-target="#generateInvoiceModal">
                <i class="fa fa-file-text-o"></i> {{ __('owner.inv.generate') }}
            </button>
        @endcan
    </div>

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
                    <th>{{ __('owner.invoices') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->church?->name }}</td>
                        <td>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                        <td>
                            <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td>{{ $payment->paid_at?->format('M d, Y H:i') ?? '—' }}</td>
                        <td>{{ $payment->provider_reference ?? '—' }}</td>
                        <td>
                            @if($payment->invoice)
                                <a href="{{ route('owner.invoices.show', $payment->invoice) }}" class="btn btn-sm btn-outline-primary">
                                    {{ $payment->invoice->invoice_number }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
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

@can('create', \App\Models\Invoice::class)
@include('owner.invoices.partials.generate-modal', ['churches' => $churches])
@endcan
@endsection
