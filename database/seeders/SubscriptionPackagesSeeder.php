<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\SubscriptionPackage;
use Illuminate\Database\Seeder;

class SubscriptionPackagesSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Essential tools for small churches.',
                'installation_price' => 29.00,
                'yearly_price' => 290.00,
                'trial_days' => 14,
                'max_members' => 200,
                'max_sms_monthly' => 100,
                'sort_order' => 1,
                'features' => ['members', 'attendance'],
            ],
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'description' => 'Full management for growing churches.',
                'installation_price' => 59.00,
                'yearly_price' => 590.00,
                'trial_days' => 14,
                'max_members' => 1000,
                'max_sms_monthly' => 500,
                'sort_order' => 2,
                'features' => ['members', 'attendance', 'finance', 'reports'],
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'description' => 'Complete suite with SMS and advanced reports.',
                'installation_price' => 99.00,
                'yearly_price' => 990.00,
                'trial_days' => 14,
                'max_members' => null,
                'max_sms_monthly' => 2000,
                'sort_order' => 3,
                'features' => ['members', 'attendance', 'finance', 'reports', 'sms', 'branches'],
            ],
        ];

        foreach ($packages as $data) {
            $featureKeys = $data['features'];
            unset($data['features']);

            $package = SubscriptionPackage::firstOrCreate(
                ['slug' => $data['slug']],
                $data,
            );

            $sync = [];
            foreach (Feature::all() as $feature) {
                $sync[$feature->id] = [
                    'is_enabled' => in_array($feature->key, $featureKeys, true),
                    'limits' => null,
                ];
            }
            $package->features()->sync($sync);
        }
    }
}
