<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            ChurchRolesAndPermissionsSeeder::class,
            FeaturesSeeder::class,
            SubscriptionPackagesSeeder::class,
            OwnerUserSeeder::class,
            DemoChurchesSeeder::class,
        ]);
    }
}
