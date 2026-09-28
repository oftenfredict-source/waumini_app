<?php

namespace App\Console\Commands;

use App\Services\Owner\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'backup:database {--force : Run even if scheduled backups are disabled}';

    protected $description = 'Dump the database and upload the backup to Google Drive';

    public function handle(DatabaseBackupService $backups): int
    {
        if (! $this->option('force') && ! $backups->isEnabled()) {
            $this->warn('Database backups are disabled in Owner Settings.');

            return self::SUCCESS;
        }

        $this->info('Starting database backup to Google Drive...');

        $result = $backups->run();
        $log = $result['log'];

        if ($result['ok']) {
            $this->info("Backup uploaded: {$log->filename}");

            return self::SUCCESS;
        }

        $this->error($log->message ?: 'Backup failed.');

        return self::FAILURE;
    }
}
