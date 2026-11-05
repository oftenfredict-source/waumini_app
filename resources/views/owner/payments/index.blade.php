@extends('layouts.owner')

@section('title', __('owner.pay.title'))

@section('content')
@php
    $currency = \App\Models\SystemSetting::platformCurrency();
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">{{ __('owner.pay.title') }}</h1>
        <p class="text-muted mb-0">{{ __('owner.pay.subtitle') }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @can('create', \App\Models\Payment::class)
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manualPaymentModal">
                <i class="bi bi-plus-lg me-1"></i> {{ __('owner.pay.record_manual') }}
            </button>
        @endcan
        @can('create', \App\Models\Invoice::class)
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generateInvoiceModal">
                <i class="bi bi-file-earmark-text me-1"></i> {{ __('owner.inv.generate') }}
            </button>
        @endcan
    </div>
</div>

<div class="row g-3 g-lg-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-list-ul"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.pay.total') }}</p>
                        <div class="h3 mb-0">{{ number_format($stats['total']) }}</div>
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
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.pay.completed') }}</p>
                        <div class="h3 mb-0">{{ number_format($stats['completed']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(217,119,6,.12);color:#d97706;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.pay.pending') }}</p>
                        <div class="h3 mb-0">{{ number_format($stats['pending']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(8,145,178,.12);color:#0891b2;">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.pay.collected') }}</p>
                        <div class="h3 mb-0">{{ $currency }} {{ number_format($stats['revenue'], 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="alert border-0 mb-4 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2" style="background:rgba(217,119,6,.1);color:#92400e;">
    <div>
        <i class="bi bi-exclamation-triangle me-1"></i>
        {{ __('owner.inv.outstanding') }}:
        <strong>{{ number_format($stats['outstanding_count']) }}</strong>
        ({{ $currency }} {{ number_format($stats['outstanding'], 0) }})
    </div>
    <a href="{{ route('owner.invoices.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-secondary">
        {{ __('owner.inv.view_debts') }}
    </a>
</div>

<div class="card owner-panel-card">
    <div class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-stretch align-items-lg-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <span>{{ __('owner.payments') }}</span>
            <span class="badge text-bg-light border">{{ $payments->total() }}</span>
        </div>
        <form method="GET" class="d-flex align-items-center flex-nowrap gap-2">
            <select name="church_id" class="form-select form-select-sm" style="width:auto;min-width:10rem;">
                <option value="">{{ __('owner.inv.all_churches') }}</option>
                @foreach($churches as $church)
                    <option value="{{ $church->id }}" @selected(($filters['church_id'] ?? '') == $church->id)>{{ $church->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-sm" style="width:auto;min-width:8rem;">
                <option value="">{{ __('pages.shared.all_statuses') }}</option>
                <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>{{ __('owner.pay.pending') }}</option>
                <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>{{ __('owner.pay.completed') }}</option>
                <option value="failed" @selected(($filters['status'] ?? '') === 'failed')>{{ __('pages.system_sms.failed') }}</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm text-nowrap">
                <i class="bi bi-funnel me-1"></i> {{ __('common.filter') }}
            </button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
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
                        @php
                            $statusStyles = [
                                'completed' => 'background:rgba(22,163,74,.12);color:#16a34a;',
                                'pending' => 'background:rgba(217,119,6,.12);color:#d97706;',
                                'failed' => 'background:rgba(220,38,38,.12);color:#dc2626;',
                            ];
                            $statusStyle = $statusStyles[$payment->status] ?? $statusStyles['pending'];
                        @endphp
                        <tr>
                            <td>
                                @if($payment->church)
                                    <a href="{{ route('owner.churches.show', $payment->church) }}" class="fw-semibold text-decoration-none" style="color:#940000;">
                                        {{ $payment->church->name }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="fw-semibold text-nowrap">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                            <td>
                                <span class="badge rounded-pill" style="{{ $statusStyle }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="text-nowrap">{{ $payment->paid_at?->format('M d, Y H:i') ?? '—' }}</td>
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
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-inbox d-block mb-2" style="font-size:1.75rem;"></i>
                                {{ __('owner.pay.no_payments') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())
        <div class="card-footer bg-white">
            {{ $payments->links() }}
        </div>
    @endif
</div>

@can('create', \App\Models\Payment::class)
@include('owner.payments.partials.manual-payment-modal', [
    'churches' => $churches,
    'pendingInvoices' => $pendingInvoices,
])
@endcan

@can('create', \App\Models\Invoice::class)
@include('owner.invoices.partials.generate-modal', ['churches' => $churches])
@endcan
@endsection
