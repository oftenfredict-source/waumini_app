<?php

namespace App\Http\Controllers\Church\System;

use App\Services\Church\ChurchSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends SystemController
{
    public function __construct(
        private readonly ChurchSettingsService $churchSettingsService,
    ) {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->can('system.settings'), 403);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $church = $this->church();
        $tab = $request->string('tab')->trim()->toString() ?: 'general';

        if (! array_key_exists($tab, config('church_settings.categories', []))) {
            $tab = 'general';
        }

        return view('church.system.settings.index', [
            'church' => $church,
            'tab' => $tab,
            'settings' => $this->churchSettingsService->all($church),
            'categories' => config('church_settings.categories'),
        ]);
    }

    public function update(Request $request, string $tab): RedirectResponse
    {
        $church = $this->church();
        $data = $this->churchSettingsService->validateTab($tab, $request->all(), $request);
        $this->churchSettingsService->updateTab($church, $tab, $data, $request);

        $label = config("church_settings.categories.{$tab}.name", ucfirst($tab));

        return redirect()
            ->route('church.system.settings.index', ['tab' => $tab])
            ->with('success', "{$label} settings saved successfully.");
    }
}
