<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeaturesSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            ['key' => 'members', 'name' => 'Members Management', 'module' => 'members'],
            ['key' => 'finance', 'name' => 'Finance Module', 'module' => 'finance'],
            ['key' => 'sms', 'name' => 'SMS System', 'module' => 'sms'],
            ['key' => 'reports', 'name' => 'Reports Access', 'module' => 'reports'],
            ['key' => 'attendance', 'name' => 'Attendance System', 'module' => 'attendance'],
            ['key' => 'branches', 'name' => 'Branches Module', 'module' => 'branches'],
        ];

        foreach ($features as $feature) {
            Feature::firstOrCreate(['key' => $feature['key']], $feature);
        }
    }
}
