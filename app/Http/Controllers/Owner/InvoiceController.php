<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\MarkInvoicePaidRequest;
use App\Http\Requests\Owner\StoreInvoiceRequest;
use App\Models\Church;
use App\Models\Invoice;
use App\Models\SystemSetting;
use App\Services\Owner\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::with(['church', 'payment'])->latest('issued_at');

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        if ($type = $request->string('type')->trim()->toString()) {
            $query->where('type', $type);
        }

        if ($churchId = $request->integer('church_id')) {
            $query->where('church_id', $churchId);
        }

        $invoices = $query->paginate(15)->withQueryString();

        return view('owner.invoices.index', [
            'invoices' => $invoices,
            'churches' => Church::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['status', 'type', 'church_id']),
            'stats' => [
                'total' => Invoice::count(),
                'pending' => Invoice::where('status', Invoice::STATUS_PENDING)->count(),
                'paid' => Invoice::where('status', Invoice::STATUS_PAID)->count(),
                'outstanding' => Invoice::where('status', Invoice::STATUS_PENDING)->sum('total_amount'),
            ],
        ]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $church = $request->route('church') ?? Church::findOrFail($request->churchId());

        $this->authorize('create', Invoice::class);

        $invoice = $this->invoiceService->generate(
            $church,
            $request->input('type'),
            $request->amount(),
            $request->user(),
            $request->invoiceNumber(),
        );

        if ($request->route('church')) {
            return redirect()
                ->route('owner.churches.show', $church)
                ->with('success', __('owner.inv.generated_success', ['number' => $invoice->invoice_number]));
        }

        return redirect()
            ->route('owner.invoices.show', $invoice)
            ->with('success', __('owner.inv.generated_success', ['number' => $invoice->invoice_number]));
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load(['church', 'subscription.package', 'payment']);

        return view('owner.invoices.show', [
            'invoice' => $invoice,
            'company' => SystemSetting::invoiceSettings(),
        ]);
    }

    public function pdf(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        $invoice->load(['church', 'subscription.package']);

        $pdf = Pdf::loadView('owner.invoices.pdf', [
            'invoice' => $invoice,
            'company' => SystemSetting::invoiceSettings(),
        ])->setPaper('a4', 'portrait');

        $filename = 'invoice-'.$invoice->invoice_number.'.pdf';

        return $pdf->download($filename);
    }

    public function markPaid(MarkInvoicePaidRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->invoiceService->markPaid(
            $invoice,
            $request->user(),
            $request->paymentInput(),
        );

        return back()->with('success', __('owner.inv.marked_paid_success', ['number' => $invoice->invoice_number]));
    }
}
