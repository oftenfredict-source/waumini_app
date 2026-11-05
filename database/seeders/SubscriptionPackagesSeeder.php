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
                'installation_price' => 1000000,
                'yearly_price' => 100000,
                'trial_days' => 14,
                'max_members' => 200,
                'max_sms_monthly' => 100,
                'sort_order' => 1,
                'features' => ['members', 'finance', 'sms', 'reports', 'attendance'],
            ],
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'description' => 'Full management for growing churches.',
                'currency' => 'TZS',
                'installation_price' => 1000000,
                'yearly_price' => 150000,
                'trial_days' => 14,
                'max_members' => 1000,
                'max_sms_monthly' => 500,
                'sort_order' => 2,
                'features' => ['members', 'finance', 'sms', 'reports', 'attendance'],
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'description' => 'Complete suite with SMS and advanced reports.',
                'currency' => 'TZS',
                'installation_price' => 1000000,
                'yearly_price' => 200000,
                'trial_days' => 14,
                'max_members' => null,
                'max_sms_monthly' => null,
                'sort_order' => 3,
                'features' => ['members', 'finance', 'sms', 'reports', 'attendance', 'branches'],
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
