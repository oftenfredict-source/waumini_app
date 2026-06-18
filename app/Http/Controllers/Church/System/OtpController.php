<?php

namespace App\Http\Controllers\Church\System;

use App\Models\LoginOtp;
use App\Services\Church\ChurchSettingsService;
use App\Services\Church\LoginOtpService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OtpController extends SystemController
{
    public function __construct(
        private readonly LoginOtpService $loginOtpService,
        private readonly ChurchSettingsService $churchSettings,
    ) {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->can('system.users'), 403);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $church = $this->church();

        $query = LoginOtp::query()
            ->where('church_id', $church->id)
            ->with('user:id,name,email,phone,member_id');

        if ($request->filled('status')) {
            match ($request->string('status')->toString()) {
                'used' => $query->where('is_used', true),
                'active' => $query->where('is_used', false)->where('expires_at', '>', now()),
                'expired' => $query->where('is_used', false)->where('expires_at', '<=', now()),
                default => null,
            };
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('otp_code', 'like', "%{$search}%")
                    ->orWhere('login_identifier', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $otps = $query->latest()->paginate(20)->withQueryString();

        $baseQuery = LoginOtp::query()->where('church_id', $church->id);

        return view('church.system.otps.index', [
            'church' => $church,
            'enabled' => $this->loginOtpService->isEnabledForChurch($church),
            'otps' => $otps,
            'stats' => [
                'total' => (clone $baseQuery)->count(),
                'active' => (clone $baseQuery)->where('is_used', false)->where('expires_at', '>', now())->count(),
                'used' => (clone $baseQuery)->where('is_used', true)->count(),
                'today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
            ],
        ]);
    }
}
