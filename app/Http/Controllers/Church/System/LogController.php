<?php

namespace App\Http\Controllers\Church\System;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends SystemController
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->can('system.logs'), 403);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $church = $this->church();

        $query = AuditLog::query()
            ->with('user')
            ->where('church_id', $church->id)
            ->latest('created_at');

        if ($action = $request->string('action')->trim()->toString()) {
            $query->where('action', 'like', "%{$action}%");
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(25)->withQueryString();
        $actions = AuditLog::query()
            ->where('church_id', $church->id)
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('church.system.logs.index', compact('church', 'logs', 'actions'));
    }
}
