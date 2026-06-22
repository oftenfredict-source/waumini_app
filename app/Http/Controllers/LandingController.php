<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPackage;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user?->isChurchPortalUser()) {
            return redirect()->route('church.dashboard');
        }

        $currencyCode = SystemSetting::platformCurrency();

        $packages = SubscriptionPackage::query()
            ->where('is_active', true)
            ->with('features')
            ->orderBy('sort_order')
            ->get();
        return view('landing.index', [
            'appName' => config('app.name') === 'Laravel' ? 'Waumini Link' : config('app.name'),
            'currencyCode' => $currencyCode,
            'packages' => $packages,
            'supportEmail' => SystemSetting::getValue('general', 'support_email', 'support@wauminilink.com'),
        ]);
    }
}
