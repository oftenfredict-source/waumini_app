<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\ChurchSubscription;
use App\Models\SubscriptionPackage;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', SubscriptionPackage::class);

        $packages = SubscriptionPackage::withCount([
            'subscriptions as active_count' => fn ($q) => $q->whereIn('status', ['trial', 'active']),
            'subscriptions as trial_count' => fn ($q) => $q->where('status', 'trial'),
        ])->orderBy('sort_order')->get();

        return view('owner.subscriptions.index', [
            'packages' => $packages,
            'subscriptions' => ChurchSubscription::with(['church', 'package'])
                ->latest()
                ->paginate(15),
            'stats' => [
                'packages' => $packages->count(),
                'active' => ChurchSubscription::where('status', 'active')->count(),
                'trial' => ChurchSubscription::where('status', 'trial')->count(),
                'expired' => ChurchSubscription::whereIn('status', ['expired', 'cancelled', 'past_due'])->count(),
            ],
        ]);
    }

    public function packages(): View
    {
        $this->authorize('viewAny', SubscriptionPackage::class);

        return view('owner.subscriptions.packages', [
            'packages' => SubscriptionPackage::with('features')->orderBy('sort_order')->get(),
        ]);
    }
}
