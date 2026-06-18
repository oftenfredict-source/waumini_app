<?php

use App\Models\Feature;
use App\Models\SubscriptionPackage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $feature = Feature::query()->firstOrCreate(
            ['key' => 'branches'],
            [
                'name' => 'Branches Module',
                'description' => 'Multi-branch church management',
                'module' => 'branches',
            ],
        );

        SubscriptionPackage::query()->each(function (SubscriptionPackage $package) use ($feature): void {
            $package->features()->syncWithoutDetaching([
                $feature->id => [
                    'is_enabled' => $package->slug === 'premium',
                    'limits' => null,
                ],
            ]);
        });
    }

    public function down(): void
    {
        $feature = Feature::query()->where('key', 'branches')->first();

        if (! $feature) {
            return;
        }

        SubscriptionPackage::query()->each(function (SubscriptionPackage $package) use ($feature): void {
            $package->features()->detach($feature->id);
        });

        $feature->delete();
    }
};
