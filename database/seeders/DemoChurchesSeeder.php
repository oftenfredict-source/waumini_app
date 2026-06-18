<?php

namespace Database\Seeders;

use App\Services\Owner\ChurchService;
use Illuminate\Database\Seeder;

class DemoChurchesSeeder extends Seeder
{
    public function run(): void
    {
        $churchService = app(ChurchService::class);
        $basicPackage = \App\Models\SubscriptionPackage::where('slug', 'basic')->first();
        $standardPackage = \App\Models\SubscriptionPackage::where('slug', 'standard')->first();

        if (! \App\Models\Church::where('slug', 'grace-community')->exists()) {
            $churchService->create([
                'name' => 'Grace Community Church',
                'slug' => 'grace-community',
                'email' => 'admin@gracecommunity.org',
                'phone' => '+255 700 000 001',
                'city' => 'Dar es Salaam',
                'country' => 'Tanzania',
                'pastor_name' => 'Rev. John Mwangi',
                'denomination' => 'Pentecostal',
                'billing_cycle' => 'monthly',
                'admin_email' => 'admin@gracecommunity.org',
            ], $basicPackage);
        }

        if (! \App\Models\Church::where('slug', 'new-life')->exists()) {
            $result = $churchService->create([
                'name' => 'New Life Assembly',
                'slug' => 'new-life',
                'email' => 'info@newlifeassembly.org',
                'phone' => '+255 700 000 002',
                'city' => 'Nairobi',
                'country' => 'Kenya',
                'pastor_name' => 'Pastor Sarah Kimani',
                'billing_cycle' => 'yearly',
                'admin_email' => 'admin@newlifeassembly.org',
            ], $standardPackage);

            $churchService->activate($result['church']);
        }
    }
}
