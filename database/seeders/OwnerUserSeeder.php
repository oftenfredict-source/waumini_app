<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerUserSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'admin@wauminilink.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'user_type' => UserType::Owner,
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        );

        $owner->assignRole('owner');
    }
}
