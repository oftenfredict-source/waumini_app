@include('owner.invoices.partials.sample-layout', ['invoice' => $invoice, 'company' => $company])

@push('styles')
<style>
.emca-invoice { max-width: 920px; margin: 0 auto; font-size: 14px; color: #000; }
.emca-main { width: 100%; border-collapse: collapse; table-layout: fixed; }
.emca-col-left { width: 50%; vertical-align: top; padding-right: 24px; }
.emca-col-right { width: 50%; vertical-align: top; text-align: right; }
.emca-header-left { margin: 0; padding: 0; }
.emca-invoice-title { font-size: 62px; font-weight: 700; color: #940000; margin: 0; padding: 0; line-height: 1; }
.emca-title-line { width: 240px; margin: 10px 0 16px 0; border-collapse: collapse; }
.emca-title-line td { background-color: #940000; height: 7px; line-height: 7px; font-size: 1px; padding: 0; }
.emca-logo { height: 190px; width: auto; display: inline-block; margin-bottom: 8px; }
.emca-company, .emca-invoice-to, .emca-meta, .emca-remarks { line-height: 1.4; }
.emca-invoice-to-block { margin-top: 56px; }
.emca-section-label { font-weight: 700; margin-bottom: 4px; }
.emca-meta { text-align: right; margin-top: 6px; }
.emca-remarks { text-align: right; margin-top: 84px; }
.emca-accent { color: #940000; font-weight: 700; }
.emca-purpose { margin: 22px 0 14px; }
.emca-items { width: 100%; border-collapse: collapse; margin-top: 8px; }
.emca-items th, .emca-items td { border: 1px solid #940000; padding: 8px 10px; }
.emca-items th { background: #940000; color: #fff; }
.emca-totals { width: 320px; margin-left: auto; margin-top: 12px; }
.emca-totals td { padding: 4px 0; }
.emca-totals .label { font-weight: 700; }
.emca-totals .value { text-align: right; }
.emca-footer { margin-top: 18px; }
@media print {
    .app-sidebar, .app-header, .app-title, .d-print-none, .modal { display: none !important; }
    .tile { border: none; box-shadow: none; padding: 0; }
}
</style>
@endpush
