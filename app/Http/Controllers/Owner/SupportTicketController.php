<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SupportTicket::class);

        $query = SupportTicket::with(['church', 'assignee'])->latest();

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        return view('owner.support.index', [
            'tickets' => $query->paginate(15)->withQueryString(),
            'stats' => [
                'open' => SupportTicket::where('status', 'open')->count(),
                'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
                'resolved' => SupportTicket::whereIn('status', ['resolved', 'closed'])->count(),
            ],
            'filters' => $request->only(['status']),
        ]);
    }
}
