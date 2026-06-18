<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Payment;
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

        $payments = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Payment::count(),
            'completed' => Payment::where('status', 'completed')->count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'revenue' => Payment::where('status', 'completed')->sum('amount'),
        ];

        return view('owner.payments.index', [
            'payments' => $payments,
            'stats' => $stats,
            'filters' => $request->only(['status']),
        ]);
    }
}
