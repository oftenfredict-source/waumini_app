<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
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

        return view('owner.payments.index', [
            'payments' => $payments,
            'churches' => Church::orderBy('name')->get(['id', 'name']),
            'stats' => $stats,
            'filters' => $request->only(['status', 'church_id']),
        ]);
    }
}
