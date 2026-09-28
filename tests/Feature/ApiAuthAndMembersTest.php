<?php

namespace Tests\Feature;

use App\Enums\MemberStatus;
use App\Enums\MembershipType;
use App\Enums\UserType;
use App\Models\Member;
use App\Models\SubscriptionPackage;
use App\Models\User;
use App\Services\Owner\ChurchService;
use Database\Seeders\ChurchRolesAndPermissionsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPackagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthAndMembersTest extends TestCase
{
    use RefreshDatabase;

    public function test_church_admin_can_login_via_api_and_list_members(): void
    {
        $church = $this->createChurch();
        $admin = $church->adminUser;
        $password = 'secret-pass';
        $admin->update(['password' => Hash::make($password)]);

        $member = Member::query()->create([
            'church_id' => $church->id,
            'member_number' => 'WL-TEST-001',
            'full_name' => 'Jane Member',
            'email' => 'jane@example.com',
            'phone_number' => '+255711111111',
            'status' => MemberStatus::Active,
            'membership_type' => MembershipType::Permanent,
        ]);

        $login = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => $password,
        ]);

        $login->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonStructure(['token', 'user' => ['id', 'email']]);

        $token = $login->json('token');

        $this->getJson('/api/user', [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk()->assertJsonPath('success', true);

        $this->getJson('/api/members?search=Jane', [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('data.0.id', $member->id);

        $this->getJson('/api/members/'.$member->id, [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk()->assertJsonPath('data.member_number', 'WL-TEST-001');
    }

    public function test_api_login_rejects_invalid_credentials(): void
    {
        $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong',
        ])->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid credentials');
    }

    public function test_members_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/members')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_church_member_without_staff_permission_cannot_list_members(): void
    {
        $church = $this->createChurch();

        $linkedMember = Member::query()->create([
            'church_id' => $church->id,
            'member_number' => 'WL-TEST-002',
            'full_name' => 'Self Member',
            'email' => 'self@example.com',
            'phone_number' => '+255722222222',
            'status' => MemberStatus::Active,
            'membership_type' => MembershipType::Permanent,
        ]);

        $user = User::factory()->create([
            'user_type' => UserType::Member,
            'church_id' => $church->id,
            'member_id' => $linkedMember->id,
            'email' => 'self-user@example.com',
            'password' => Hash::make('password'),
        ]);

        $token = $user->createToken('flutter')->plainTextToken;

        $this->getJson('/api/members', [
            'Authorization' => 'Bearer '.$token,
        ])->assertForbidden();

        $this->getJson('/api/members/'.$linkedMember->id, [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk()->assertJsonPath('data.id', $linkedMember->id);
    }

    private function createChurch()
    {
        $this->seed([
            RolesAndPermissionsSeeder::class,
            ChurchRolesAndPermissionsSeeder::class,
            SubscriptionPackagesSeeder::class,
        ]);

        $package = SubscriptionPackage::where('slug', 'basic')->firstOrFail();

        $result = app(ChurchService::class)->create([
            'name' => 'API Test Church',
            'slug' => 'api-test-church',
            'email' => 'contact@apitest.org',
            'phone' => '+255700000099',
            'admin_email' => 'admin@apitest.org',
            'pastor_name' => 'Pastor API',
            'billing_cycle' => 'monthly',
        ], $package);

        return $result['church'];
    }
}
