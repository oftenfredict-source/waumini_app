<?php

namespace App\Http\Controllers\Church\System;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SessionController extends SystemController
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->can('system.sessions'), 403);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $churchUserIds = $this->churchUserIds();

        $sessions = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->whereIn('sessions.user_id', $churchUserIds)
            ->where('sessions.last_activity', '>', now()->subHours(24)->timestamp)
            ->select('sessions.*', 'users.name', 'users.email', 'users.user_type')
            ->orderByDesc('sessions.last_activity')
            ->get()
            ->map(function ($session) {
                $session->last_activity_human = Carbon::createFromTimestamp($session->last_activity)->diffForHumans();
                $session->is_current = $session->id === session()->getId();

                return $session;
            });

        return view('church.system.sessions.index', [
            'church' => $this->church(),
            'sessions' => $sessions,
        ]);
    }

    public function revoke(Request $request, string $sessionId): RedirectResponse
    {
        $churchUserIds = $this->churchUserIds();

        if ($sessionId === session()->getId()) {
            return back()->with('error', 'You cannot revoke your own active session.');
        }

        $deleted = DB::table('sessions')
            ->where('id', $sessionId)
            ->whereIn('user_id', $churchUserIds)
            ->delete();

        if (! $deleted) {
            return back()->with('error', 'Session not found or already expired.');
        }

        return back()->with('success', 'Session revoked successfully.');
    }
}
