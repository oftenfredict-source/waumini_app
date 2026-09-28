@extends('layouts.owner')

@section('title', __('owner.invoices'))

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-file-text-o"></i> {{ __('owner.invoices') }}</h1>
        <p>{{ __('owner.inv.subtitle') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">{{ __('owner.overview') }}</a></li>
        <li class="breadcrumb-item">{{ __('owner.invoices') }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon fa fa-list fa-3x"></i>
            <div class="info"><h4>{{ __('owner.inv.total') }}</h4><p><b>{{ $stats['total'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon fa fa-clock-o fa-3x"></i>
            <div class="info"><h4>{{ __('owner.inv.debt') }}</h4><p><b>{{ $stats['pending'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small success coloured-icon">
            <i class="icon fa fa-check fa-3x"></i>
            <div class="info"><h4>{{ __('owner.inv.paid') }}</h4><p><b>{{ $stats['paid'] }}</b></p></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="widget-small info coloured-icon">
            <i class="icon fa fa-money fa-3x"></i>
            <div class="info">
                <h4>{{ __('owner.inv.outstanding') }}</h4>
                <p><b>{{ \App\Models\SystemSetting::platformCurrency() }} {{ number_format($stats['outstanding'], 0) }}</b></p>
            </div>
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
                <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>{{ __('owner.inv.debt') }}</option>
                <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>{{ __('owner.inv.paid') }}</option>
            </select>
            <select name="type" class="form-control mr-2 mb-2">
                <option value="">{{ __('owner.inv.all_types') }}</option>
                <option value="installation" @selected(($filters['type'] ?? '') === 'installation')>{{ __('owner.inv.installation') }}</option>
                <option value="yearly" @selected(($filters['type'] ?? '') === 'yearly')>{{ __('owner.inv.annual') }}</option>
            </select>
            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-filter"></i> {{ __('common.filter') }}</button>
        </form>

        @can('create', \App\Models\Invoice::class)
            <button type="button" class="btn btn-success mb-2" data-toggle="modal" data-target="#generateInvoiceModal">
                <i class="fa fa-plus"></i> {{ __('owner.inv.generate') }}
            </button>
        @endcan
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>{{ __('owner.inv.number') }}</th>
                    <th>{{ __('owner.church_label') }}</th>
                    <th>{{ __('common.type') }}</th>
                    <th>{{ __('common.amount') }}</th>
                    <th>{{ __('owner.status') }}</th>
                    <th>{{ __('owner.inv.issued_at') }}</th>
                    <th>{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td><code>{{ $invoice->invoice_number }}</code></td>
                        <td>{{ $invoice->church?->name }}</td>
                        <td>{{ $invoice->typeLabel() }}</td>
                        <td>{{ $invoice->currency }} {{ number_format($invoice->total_amount, 0) }}</td>
                        <td>
                            <span class="badge badge-{{ $invoice->isPaid() ? 'success' : 'warning' }}">
                                {{ $invoice->isPaid() ? __('owner.inv.paid') : __('owner.inv.debt') }}
                            </span>
                        </td>
                        <td>{{ $invoice->issued_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('owner.invoices.show', $invoice) }}" class="btn btn-sm btn-info">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="{{ route('owner.invoices.pdf', $invoice) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-download"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x d-block mb-2"></i>
                            {{ __('owner.inv.no_invoices') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $invoices->links() }}
</div>

@can('create', \App\Models\Invoice::class)
@include('owner.invoices.partials.generate-modal', ['churches' => $churches])
@endcan
@endsection
