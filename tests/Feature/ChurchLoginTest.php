<?php

namespace Tests\Feature;

use App\Models\SubscriptionPackage;
use App\Services\Owner\ChurchService;
use Database\Seeders\ChurchRolesAndPermissionsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPackagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChurchLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_church_admin_can_sign_in_with_generated_credentials(): void
    {
        $this->seed([
            RolesAndPermissionsSeeder::class,
            ChurchRolesAndPermissionsSeeder::class,
            SubscriptionPackagesSeeder::class,
        ]);

        $package = SubscriptionPackage::where('slug', 'basic')->firstOrFail();

        $result = app(ChurchService::class)->create([
            'name' => 'Test Church',
            'slug' => 'test-church',
            'email' => 'contact@testchurch.org',
            'phone' => '+255700000001',
            'admin_email' => 'admin@testchurch.org',
            'pastor_name' => 'Pastor Test',
            'billing_cycle' => 'monthly',
        ], $package);

        $church = $result['church'];
        $password = $result['admin_password'];

        $this->assertNotEmpty($password);
        $this->assertSame('admin@testchurch.org', $church->adminUser->email);
        $this->assertSame('+255700000001', $church->adminUser->phone);

        $response = $this->post(route('church.login.submit'), [
            'email' => 'admin@testchurch.org',
            'password' => $password,
        ]);

        $response->assertRedirect(route('church.dashboard'));
        $this->assertAuthenticatedAs($church->adminUser);
    }
}
