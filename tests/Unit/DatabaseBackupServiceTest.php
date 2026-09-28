<?php

namespace Tests\Unit;

use App\Models\DatabaseBackupLog;
use App\Services\Owner\DatabaseBackupService;
use App\Services\Owner\GoogleDriveBackupClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DatabaseBackupServiceTest extends TestCase
{
    public function test_authorization_url_uses_oauth_web_client(): void
    {
        $url = GoogleDriveBackupClient::authorizationUrl(
            'test-client-id',
            'https://example.test/owner/settings/backup/google/callback',
            'state-token',
        );

        $this->assertStringContainsString('accounts.google.com/o/oauth2/v2/auth', $url);
        $this->assertStringContainsString('client_id=test-client-id', $url);
        $this->assertStringContainsString('access_type=offline', $url);
        $this->assertStringContainsString('state=state-token', $url);
    }

    public function test_sms_message_reports_success_and_failure(): void
    {
        $service = $this->app->make(DatabaseBackupService::class);

        $ok = new DatabaseBackupLog([
            'filename' => 'waumini-link-2026-09-28.sql.gz',
            'size_bytes' => 1048576,
            'status' => DatabaseBackupLog::STATUS_SUCCESS,
            'message' => 'Uploaded to Google Drive.',
        ]);

        $this->assertStringContainsString('imefanikiwa', $service->smsMessage($ok));
        $this->assertStringContainsString('1.00 MB', $service->smsMessage($ok));

        $fail = new DatabaseBackupLog([
            'filename' => 'waumini-link-2026-09-28.sql.gz',
            'status' => DatabaseBackupLog::STATUS_FAILED,
            'message' => 'Connect Google Drive first.',
        ]);

        $this->assertStringContainsString('imeshindikana', $service->smsMessage($fail));
        $this->assertStringContainsString('Connect Google Drive first.', $service->smsMessage($fail));
    }

    public function test_rotated_refresh_token_is_persisted(): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'ya29.new-access',
                'refresh_token' => '1//rotated-refresh',
                'expires_in' => 3600,
            ], 200),
            'https://www.googleapis.com/oauth2/v2/userinfo' => Http::response([
                'email' => 'owner@example.com',
            ], 200),
        ]);

        $saved = null;
        $client = new GoogleDriveBackupClient(
            [
                'client_id' => 'client-id',
                'client_secret' => 'client-secret',
                'refresh_token' => '1//old-refresh',
            ],
            function (string $token) use (&$saved): void {
                $saved = $token;
            },
        );

        $this->assertSame('owner@example.com', $client->accountEmail());
        $this->assertSame('1//rotated-refresh', $saved);
    }

    public function test_unauthorized_client_explains_credential_mismatch(): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'error' => 'unauthorized_client',
                'error_description' => 'Unauthorized',
            ], 401),
        ]);

        $client = new GoogleDriveBackupClient([
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'refresh_token' => '1//other-app-token',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('unauthorized_client');
        $client->accountEmail();
    }

    public function test_normalize_credential_strips_labels_and_whitespace(): void
    {
        $this->assertSame(
            '1//abc',
            GoogleDriveBackupClient::normalizeCredential("Refresh token: \n1//abc\n"),
        );
    }

    public function test_folder_name_is_not_treated_as_drive_id(): void
    {
        $this->assertFalse(GoogleDriveBackupClient::looksLikeDriveId('aict_backup'));
        $this->assertTrue(GoogleDriveBackupClient::looksLikeDriveId('1AbCDefGhijKLmnoPQRSTUV'));
    }
}
