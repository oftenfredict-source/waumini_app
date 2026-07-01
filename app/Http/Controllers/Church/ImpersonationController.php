<?php

namespace App\Http\Controllers\Church;

use App\Http\Controllers\Controller;
use App\Services\Owner\ChurchImpersonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    public function __construct(
        private readonly ChurchImpersonationService $impersonationService,
    ) {}

    public function leave(Request $request): RedirectResponse
    {
        if (! $this->impersonationService->isActive($request)) {
            return redirect()->route('church.dashboard');
        }

        return $this->impersonationService->stop($request);
    }
}
