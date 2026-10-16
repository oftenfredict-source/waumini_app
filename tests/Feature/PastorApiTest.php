<?php

namespace Tests\Feature;

use App\Enums\AnnouncementTargetType;
use App\Enums\AnnouncementType;
use App\Enums\FinancialApprovalStatus;
use App\Enums\MemberStatus;
use App\Enums\MembershipType;
use App\Enums\UserType;
use App\Models\Announcement;
use App\Models\Member;
use App\Models\SubscriptionPackage;
use App\Models\Tithe;
use App\Models\User;
use App\Services\Church\ChurchStaffUserService;
use App\Services\Owner\ChurchService;
use Database\Seeders\ChurchRolesAndPermissionsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPackagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PastorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_pastor_can_retrieve_dashboard_members_announcements_and_approvals(): void
    {
        [$church, $pastor, $password] = $this->createPastor();

        $member = Member::query()->create([
            'church_id' => $church->id,
            'member_number' => 'WL-PASTOR-001',
            'full_name' => 'Amina Member',
            'email' => 'amina@example.com',
            'phone_number' => '+255733333333',
            'status' => MemberStatus::Active,
            'membership_type' => MembershipType::Permanent,
        ]);

        Announcement::query()->create([
            'church_id' => $church->id,
            'title' => 'Sunday Notice',
            'content' => 'Bring your Bible.',
            'type' => AnnouncementType::General,
            'target_type' => AnnouncementTargetType::All,
            'is_active' => true,
            'created_by' => $pastor->id,
        ]);

        Tithe::query()->create([
            'church_id' => $church->id,
            'member_id' => $member->id,
            'amount' => 15000,
            'tithe_date' => now()->toDateString(),
            'approval_status' => FinancialApprovalStatus::Pending,
            'recorded_by' => $pastor->id,
        ]);

        $login = $this->postJson('/api/login', [
            'email' => $pastor->email,
            'password' => $password,
            'device_name' => 'pastor-app',
        ]);

        $login->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.user_type', UserType::Pastor->value);

        $token = $login->json('token');
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->getJson('/api/pastor/dashboard', $headers)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.role.is_pastor', true)
            ->assertJsonPath('data.stats.total_members', 1)
            ->assertJsonPath('data.pending_approvals.total_pending', 1)
            ->assertJsonPath('data.announcements.0.title', 'Sunday Notice');

        $this->getJson('/api/pastor/members?search=Amina', $headers)
            ->assertOk()
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('data.0.member_number', 'WL-PASTOR-001');

        $this->getJson('/api/pastor/members/'.$member->id, $headers)
            ->assertOk()
            ->assertJsonPath('data.full_name', 'Amina Member');

        $this->getJson('/api/pastor/announcements', $headers)
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Sunday Notice');

        $this->getJson('/api/pastor/finance', $headers)
            ->assertOk()
            ->assertJsonPath('data.currency', $church->currency ?? 'TZS')
            ->assertJsonStructure(['data' => ['period', 'summary', 'income_breakdown']]);

        $this->getJson('/api/pastor/finance/approvals', $headers)
            ->assertOk()
            ->assertJsonPath('data.total_pending', 1)
            ->assertJsonPath('data.pending.tithe.0.member', 'Amina Member')
            ->assertJsonPath('data.pending.tithe.0.amount', 15000);
    }

    public function test_assistant_pastor_cannot_retrieve_finance_approvals(): void
    {
        $church = $this->createChurch('assistant-api-church', 'assistant@apitest.org');
        $created = app(ChurchStaffUserService::class)->create($church, [
            'name' => 'Assistant Pastor',
            'email' => 'assistant.pastor@apitest.org',
            'role' => 'assistant_pastor',
        ]);

        $token = $created['user']->createToken('pastor-app')->plainTextToken;

        $this->getJson('/api/pastor/dashboard', [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk()->assertJsonPath('data.role.is_assistant_pastor', true);

        $this->getJson('/api/pastor/finance/approvals', [
            'Authorization' => 'Bearer '.$token,
        ])->assertForbidden()->assertJsonPath('success', false);
    }

    public function test_members_and_other_staff_cannot_use_the_pastor_api(): void
    {
        $church = $this->createChurch('staff-api-church', 'staff-admin@apitest.org');

        $linkedMember = Member::query()->create([
            'church_id' => $church->id,
            'member_number' => 'WL-PASTOR-002',
            'full_name' => 'Self Member',
            'status' => MemberStatus::Active,
            'membership_type' => MembershipType::Permanent,
        ]);

        $memberUser = User::factory()->create([
            'user_type' => UserType::Member,
            'church_id' => $church->id,
            'member_id' => $linkedMember->id,
            'password' => Hash::make('password'),
        ]);

        $secretary = app(ChurchStaffUserService::class)->create($church, [
            'name' => 'Church Secretary',
            'email' => 'secretary@apitest.org',
            'role' => 'secretary',
        ])['user'];

        $this->getJson('/api/pastor/dashboard')->assertUnauthorized();

        $this->getJson('/api/pastor/dashboard', [
            'Authorization' => 'Bearer '.$memberUser->createToken('flutter')->plainTextToken,
        ])->assertForbidden()->assertJsonPath('message', 'This area is for pastors only.');

        $this->getJson('/api/pastor/members', [
            'Authorization' => 'Bearer '.$secretary->createToken('flutter')->plainTextToken,
        ])->assertForbidden()->assertJsonPath('message', 'This area is for pastors only.');
    }

    public function test_pastor_can_register_a_member_and_service_with_the_same_fields_as_the_web_form(): void
    {
        [, $pastor] = $this->createPastor();
        $token = $pastor->createToken('pastor-app')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->getJson('/api/pastor/members/form', $headers)
            ->assertOk()
            ->assertJsonPath('data.fields.0', 'membership_type')
            ->assertJsonFragment(['value' => 'permanent']);

        $this->postJson('/api/pastor/members', [
            'full_name' => 'Neema Pastor',
        ], $headers)->assertUnprocessable()
            ->assertJsonValidationErrors(['gender', 'date_of_birth', 'phone_number', 'membership_type', 'marital_status']);

        $this->postJson('/api/pastor/members', [
            'membership_type' => 'permanent',
            'member_type' => 'father',
            'full_name' => 'Neema Pastor',
            'gender' => 'male',
            'date_of_birth' => '1992-04-04',
            'phone_number' => '+255744444444',
            'envelope_number' => '101',
            'email' => 'neema@example.com',
            'profession' => 'Teacher',
            'region' => 'Dar es Salaam',
            'district' => 'Ilala',
            'marital_status' => 'single',
            'is_baptized' => true,
            'baptism_place' => 'Mwanza',
        ], $headers)->assertCreated()
            ->assertJsonPath('data.full_name', 'Neema Pastor')
            ->assertJsonPath('data.profession', 'Teacher')
            ->assertJsonPath('data.region', 'Dar es Salaam')
            ->assertJsonPath('data.baptism_place', 'Mwanza');

        $this->postJson('/api/pastor/services', [
            'service_type' => 'sunday',
            'service_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'theme' => 'Imani',
            'preacher_type' => 'guest',
            'preacher_guest_name' => 'Guest Preacher',
            'preacher_guest_phone' => '+255755555555',
            'venue' => 'Main hall',
        ], $headers)->assertCreated()
            ->assertJsonPath('data.theme', 'Imani')
            ->assertJsonPath('data.venue', 'Main hall')
            ->assertJsonPath('data.service_type', 'sunday');
    }

    /**
     * @return array{0: \App\Models\Church, 1: User, 2: string}
     */
    private function createPastor(): array
    {
        $church = $this->createChurch('pastor-api-church', 'pastor-admin@apitest.org');
        $created = app(ChurchStaffUserService::class)->create($church, [
            'name' => 'Pastor James',
            'email' => 'pastor.james@apitest.org',
            'role' => 'pastor',
        ]);

        return [$church, $created['user'], $created['password']];
    }

    private function createChurch(string $slug, string $adminEmail)
    {
        $this->seed([
            RolesAndPermissionsSeeder::class,
            ChurchRolesAndPermissionsSeeder::class,
            SubscriptionPackagesSeeder::class,
        ]);

        $package = SubscriptionPackage::where('slug', 'basic')->firstOrFail();

        return app(ChurchService::class)->create([
            'name' => 'Pastor API Church',
            'slug' => $slug,
            'email' => 'contact+'.$slug.'@apitest.org',
            'phone' => '+255700000088',
            'admin_email' => $adminEmail,
            'pastor_name' => 'Pastor API',
            'billing_cycle' => 'monthly',
        ], $package)['church'];
    }
}
