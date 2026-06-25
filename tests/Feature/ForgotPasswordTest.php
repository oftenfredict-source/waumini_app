<?php

namespace Tests\Feature;

use App\Models\PasswordResetOtp;
use App\Models\SubscriptionPackage;
use App\Models\SystemSetting;
use App\Services\Owner\ChurchService;
use App\Services\Sms\SmsGatewayService;
use Database\Seeders\ChurchRolesAndPermissionsSeeder;
use Database\Seeders\FeaturesSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPackagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RolesAndPermissionsSeeder::class,
            ChurchRolesAndPermissionsSeeder::class,
            FeaturesSeeder::class,
            SubscriptionPackagesSeeder::class,
        ]);

        SystemSetting::setValue('notifications', 'sms_enabled', true);
        SystemSetting::setValue('sms', 'username', 'test-user');
        SystemSetting::setValue('sms', 'password', 'test-pass');
        SystemSetting::setValue('sms', 'sender_id', 'TEST');
        SystemSetting::setValue('sms', 'api_url', 'https://sms.example.com/send');

        $this->mock(SmsGatewayService::class, function ($mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('send')->andReturn(['ok' => true, 'body' => 'OK']);
        });
    }

    public function test_church_user_can_reset_password_via_sms_otp(): void
    {
        $package = SubscriptionPackage::where('slug', 'premium')->firstOrFail();

        $result = app(ChurchService::class)->create([
            'name' => 'Reset Church',
            'slug' => 'reset-church',
            'email' => 'contact@resetchurch.org',
            'phone' => '+255700000099',
            'admin_email' => 'admin@resetchurch.org',
            'pastor_name' => 'Pastor Reset',
            'billing_cycle' => 'monthly',
        ], $package);

        $church = $result['church'];
        $user = $church->adminUser;
        $oldPassword = $result['admin_password'];

        $response = $this->post(route('church.password.forgot.send'), [
            'email' => 'admin@resetchurch.org',
        ]);

        $response->assertRedirect(route('church.password.forgot.verify'));
        $response->assertSessionHas('password_reset_user_id', $user->id);

        $otp = PasswordResetOtp::query()->where('user_id', $user->id)->latest()->first();
        $this->assertNotNull($otp);

        $response = $this->withSession([
            'password_reset_user_id' => $user->id,
            'password_reset_login_identifier' => 'admin@resetchurch.org',
            'password_reset_resend_attempts' => 0,
            'password_reset_verified' => false,
        ])->post(route('church.password.forgot.verify.submit'), [
            'otp' => $otp->otp_code,
        ]);

        $response->assertRedirect(route('church.password.forgot.reset'));
        $response->assertSessionHas('password_reset_verified', true);

        $newPassword = 'NewSecurePass1';

        $response = $this->withSession([
            'password_reset_user_id' => $user->id,
            'password_reset_login_identifier' => 'admin@resetchurch.org',
            'password_reset_verified' => true,
        ])->post(route('church.password.forgot.reset.submit'), [
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertRedirect(route('church.login'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
        $this->assertFalse(Hash::check($oldPassword, $user->password));

        $loginResponse = $this->post(route('church.login.submit'), [
            'email' => 'admin@resetchurch.org',
            'password' => $newPassword,
        ]);

        $loginResponse->assertRedirect(route('church.dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
