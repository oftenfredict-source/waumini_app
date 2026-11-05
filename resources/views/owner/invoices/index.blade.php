@extends('layouts.owner')

@section('title', __('owner.invoices'))

@section('content')
@php
    $currency = \App\Models\SystemSetting::platformCurrency();
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">{{ __('owner.invoices') }}</h1>
        <p class="text-muted mb-0">{{ __('owner.inv.subtitle') }}</p>
    </div>
    @can('create', \App\Models\Invoice::class)
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generateInvoiceModal">
            <i class="bi bi-plus-lg me-1"></i> {{ __('owner.inv.generate') }}
        </button>
    @endcan
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
                        <p class="h6 mb-0 text-muted">{{ __('owner.inv.total') }}</p>
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
                    <div class="stats-icon" style="background:rgba(217,119,6,.12);color:#d97706;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.inv.debt') }}</p>
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
                    <div class="stats-icon" style="background:rgba(22,163,74,.12);color:#16a34a;">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.inv.paid') }}</p>
                        <div class="h3 mb-0">{{ number_format($stats['paid']) }}</div>
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
                        <p class="h6 mb-0 text-muted">{{ __('owner.inv.outstanding') }}</p>
                        <div class="h3 mb-0">{{ $currency }} {{ number_format($stats['outstanding'], 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card owner-panel-card">
    <div class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-stretch align-items-lg-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <span>{{ __('owner.invoices') }}</span>
            <span class="badge text-bg-light border">{{ $invoices->total() }}</span>
        </div>
        <form method="GET" class="d-flex align-items-center flex-nowrap gap-2">
            <select name="church_id" class="form-select form-select-sm" style="width:auto;min-width:9rem;">
                <option value="">{{ __('owner.inv.all_churches') }}</option>
                @foreach($churches as $church)
                    <option value="{{ $church->id }}" @selected(($filters['church_id'] ?? '') == $church->id)>{{ $church->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-sm" style="width:auto;min-width:7rem;">
                <option value="">{{ __('pages.shared.all_statuses') }}</option>
                <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>{{ __('owner.inv.debt') }}</option>
                <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>{{ __('owner.inv.paid') }}</option>
            </select>
            <select name="type" class="form-select form-select-sm" style="width:auto;min-width:7rem;">
                <option value="">{{ __('owner.inv.all_types') }}</option>
                <option value="installation" @selected(($filters['type'] ?? '') === 'installation')>{{ __('owner.inv.installation') }}</option>
                <option value="yearly" @selected(($filters['type'] ?? '') === 'yearly')>{{ __('owner.inv.annual') }}</option>
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
                        <th>{{ __('owner.inv.number') }}</th>
                        <th>{{ __('owner.church_label') }}</th>
                        <th>{{ __('common.type') }}</th>
                        <th>{{ __('common.amount') }}</th>
                        <th>{{ __('owner.status') }}</th>
                        <th>{{ __('owner.inv.issued_at') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td><code class="small">{{ $invoice->invoice_number }}</code></td>
                            <td>
                                @if($invoice->church)
                                    <a href="{{ route('owner.churches.show', $invoice->church) }}" class="fw-semibold text-decoration-none" style="color:#940000;">
                                        {{ $invoice->church->name }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $invoice->typeLabel() }}</td>
                            <td class="fw-semibold text-nowrap">{{ $invoice->currency }} {{ number_format($invoice->total_amount, 0) }}</td>
                            <td>
                                @if($invoice->isPaid())
                                    <span class="badge rounded-pill" style="background:rgba(22,163,74,.12);color:#16a34a;">{{ __('owner.inv.paid') }}</span>
                                @else
                                    <span class="badge rounded-pill" style="background:rgba(217,119,6,.12);color:#d97706;">{{ __('owner.inv.debt') }}</span>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ $invoice->issued_at->format('M d, Y') }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('owner.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary" title="{{ __('common.view') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('owner.invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('owner.inv.download_pdf') }}">
                                    <i class="bi bi-download"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-inbox d-block mb-2" style="font-size:1.75rem;"></i>
                                {{ __('owner.inv.no_invoices') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())
        <div class="card-footer bg-white">
            {{ $invoices->links() }}
        </div>
    @endif
</div>

@can('create', \App\Models\Invoice::class)
@include('owner.invoices.partials.generate-modal', ['churches' => $churches])
@endcan
@endsection
