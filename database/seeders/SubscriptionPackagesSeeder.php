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
                'currency' => 'TZS',
                'installation_price' => 200000,
                'yearly_price' => 600000,
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
                'currency' => 'TZS',
                'installation_price' => 400000,
                'yearly_price' => 1500000,
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
                'currency' => 'TZS',
                'installation_price' => 600000,
                'yearly_price' => 3000000,
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

            $package = SubscriptionPackage::updateOrCreate(
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
