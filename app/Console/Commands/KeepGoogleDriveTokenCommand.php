<?php

namespace App\Console\Commands;

use App\Services\Owner\DatabaseBackupService;
use Illuminate\Console\Command;
use Throwable;

class KeepGoogleDriveTokenCommand extends Command
{
    protected $signature = 'backup:keep-google-token';

    protected $description = 'Refresh the Google Drive access token so the stored refresh token stays valid';

    public function handle(DatabaseBackupService $backups): int
    {
        if (! $backups->isConfigured()) {
            $this->comment('Google Drive is not configured yet.');

            return self::SUCCESS;
        }

        try {
            $backups->keepGoogleTokenAlive();
            $this->info('Google Drive refresh token is active.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
