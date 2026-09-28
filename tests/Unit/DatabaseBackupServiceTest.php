<?php

namespace Tests\Unit;

use App\Services\Owner\GoogleDriveBackupClient;
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
}
