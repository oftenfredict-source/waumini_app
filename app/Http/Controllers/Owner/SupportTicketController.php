<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SupportTicket::class);

        $query = SupportTicket::with(['church', 'assignee', 'creator'])->latest();

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

    public function show(SupportTicket $support): View
    {
        $this->authorize('view', $support);

        $support->load(['church', 'assignee', 'creator']);

        return view('owner.support.show', [
            'ticket' => $support,
        ]);
    }

    public function update(Request $request, SupportTicket $support): RedirectResponse
    {
        $this->authorize('update', $support);

        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'waiting', 'resolved', 'closed'])],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
        ]);

        $updates = [
            'status' => $data['status'],
            'assigned_to_user_id' => $request->user()->id,
        ];

        if (! empty($data['priority'])) {
            $updates['priority'] = $data['priority'];
        }

        if (in_array($data['status'], ['resolved', 'closed'], true)) {
            $updates['closed_at'] = now();
        } else {
            $updates['closed_at'] = null;
        }

        $support->update($updates);

        return redirect()
            ->route('owner.support.show', $support)
            ->with('success', __('owner.sup.updated'));
    }
}
