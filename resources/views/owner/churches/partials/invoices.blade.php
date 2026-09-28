@can('viewAny', \App\Models\Invoice::class)
<div class="tile">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <div>
            <h3 class="tile-title mb-1"><i class="fa fa-file-text-o"></i> {{ __('owner.invoices') }}</h3>
            <p class="text-muted mb-0">{{ __('owner.inv.church_help') }}</p>
        </div>
        @can('create', \App\Models\Invoice::class)
            <div class="mt-2 mt-md-0">
                <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#generateInvoiceModal"
                        onclick="document.getElementById('invoice_type_select').value='installation'">
                    <i class="fa fa-plus"></i> {{ __('owner.inv.installation_invoice') }}
                </button>
                <button type="button" class="btn btn-sm btn-outline-success ml-1" data-toggle="modal" data-target="#generateInvoiceModal"
                        onclick="document.getElementById('invoice_type_select').value='yearly'">
                    <i class="fa fa-plus"></i> {{ __('owner.inv.annual_invoice') }}
                </button>
            </div>
        @endcan
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered mb-0">
            <thead>
                <tr>
                    <th>{{ __('owner.inv.number') }}</th>
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
                        <td>{{ $invoice->typeLabel() }}</td>
                        <td>{{ $invoice->currency }} {{ number_format($invoice->total_amount, 0) }}</td>
                        <td>
                            <span class="badge badge-{{ $invoice->isPaid() ? 'success' : 'warning' }}">
                                {{ $invoice->isPaid() ? __('owner.inv.paid') : __('owner.inv.debt') }}
                            </span>
                        </td>
                        <td>{{ $invoice->issued_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('owner.invoices.show', $invoice) }}" class="btn btn-sm btn-info" title="{{ __('common.view') }}">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="{{ route('owner.invoices.pdf', $invoice) }}" class="btn btn-sm btn-primary" title="{{ __('owner.inv.download_pdf') }}">
                                <i class="fa fa-download"></i>
                            </a>
                            @can('markPaid', $invoice)
                                <form method="POST" action="{{ route('owner.invoices.mark-paid', $invoice) }}" class="d-inline"
                                      data-swal-confirm="{{ __('owner.inv.mark_paid_confirm') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="{{ __('owner.inv.mark_as_paid') }}">
                                        <i class="fa fa-check"></i>
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">{{ __('owner.inv.no_invoices') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@can('create', \App\Models\Invoice::class)
    @include('owner.invoices.partials.generate-modal', [
        'churches' => collect([$church]),
        'formAction' => route('owner.churches.invoices.store', $church),
        'fixedChurch' => true,
    ])
@endcan
@endcan
