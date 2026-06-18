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

        return view('owner.subscriptions.index', [
            'packages' => SubscriptionPackage::withCount([
                'subscriptions as active_count' => fn ($q) => $q->whereIn('status', ['trial', 'active']),
            ])->orderBy('sort_order')->get(),
            'subscriptions' => ChurchSubscription::with(['church', 'package'])
                ->latest()
                ->paginate(15),
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
