<?php

namespace App\Http\Controllers\Church;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SupportTicket::class);

        $church = $request->user()->church;

        $query = SupportTicket::query()
            ->where('church_id', $church->id)
            ->with(['creator', 'assignee'])
            ->latest();

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        return view('church.system.support.index', [
            'tickets' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['status']),
            'stats' => [
                'open' => SupportTicket::where('church_id', $church->id)->where('status', 'open')->count(),
                'in_progress' => SupportTicket::where('church_id', $church->id)->where('status', 'in_progress')->count(),
                'resolved' => SupportTicket::where('church_id', $church->id)->whereIn('status', ['resolved', 'closed'])->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', SupportTicket::class);

        return view('church.system.support.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SupportTicket::class);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'category' => ['required', Rule::in(['general', 'billing', 'technical', 'account', 'other'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $user = $request->user();

        $ticket = SupportTicket::create([
            'church_id' => $user->church_id,
            'created_by_user_id' => $user->id,
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => 'open',
            'subject' => $data['subject'],
            'body' => $data['body'],
        ]);

        $this->notifyOwner($ticket->load(['church', 'creator']));

        return redirect()
            ->route('church.system.support.show', $ticket)
            ->with('success', __('pages.support.created'));
    }

    public function show(SupportTicket $support): View
    {
        $this->authorize('view', $support);

        $support->load(['creator', 'assignee', 'church']);

        return view('church.system.support.show', [
            'ticket' => $support,
        ]);
    }

    private function notifyOwner(SupportTicket $ticket): void
    {
        $email = trim((string) SystemSetting::getValue('general', 'support_email', config('mail.from.address')));

        if ($email === '') {
            return;
        }

        $body = implode("\n", [
            'A new support ticket was submitted by a church.',
            '',
            'Church: '.($ticket->church?->name ?? '—'),
            'From: '.($ticket->creator?->name ?? '—').' ('.($ticket->creator?->email ?? '—').')',
            'Category: '.$ticket->category,
            'Priority: '.$ticket->priority,
            'Subject: '.$ticket->subject,
            '',
            $ticket->body,
            '',
            'Review it in the owner panel under Support.',
        ]);

        try {
            Mail::raw($body, function ($message) use ($email, $ticket): void {
                $message->to($email)
                    ->subject('Support ticket: '.$ticket->subject);
            });
        } catch (\Throwable) {
            // Ticket is saved even if mail fails.
        }
    }
}
