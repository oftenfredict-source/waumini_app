<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreManualPaymentRequest;
use App\Models\Church;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Owner\ManualPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PaymentController extends Controller
{
    public function __construct(
        private readonly ManualPaymentService $manualPaymentService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Payment::class);

        $query = Payment::with(['church', 'subscription.package'])->latest();

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        if ($churchId = $request->integer('church_id')) {
            $query->where('church_id', $churchId);
        }

        $payments = $query->with('invoice')->paginate(15)->withQueryString();

        $stats = [
            'total' => Payment::count(),
            'completed' => Payment::where('status', 'completed')->count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'revenue' => Payment::where('status', 'completed')->sum('amount'),
            'outstanding' => Invoice::where('status', Invoice::STATUS_PENDING)->sum('total_amount'),
            'outstanding_count' => Invoice::where('status', Invoice::STATUS_PENDING)->count(),
        ];

        $pendingInvoices = Invoice::query()
            ->with('church:id,name')
            ->where('status', Invoice::STATUS_PENDING)
            ->latest()
            ->get(['id', 'church_id', 'invoice_number', 'type', 'total_amount', 'currency']);

        return view('owner.payments.index', [
            'payments' => $payments,
            'churches' => Church::orderBy('name')->get(['id', 'name']),
            'pendingInvoices' => $pendingInvoices,
            'stats' => $stats,
            'filters' => $request->only(['status', 'church_id']),
        ]);
    }

    public function store(StoreManualPaymentRequest $request): RedirectResponse
    {
        try {
            $payment = $this->manualPaymentService->record($request->paymentInput(), $request->user());
        } catch (InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        $activated = (bool) data_get($payment->metadata, 'subscription_activated');

        return redirect()
            ->route('owner.payments.index')
            ->with('success', $activated
                ? __('owner.pay.manual_recorded_activated')
                : __('owner.pay.manual_recorded'));
    }
}
