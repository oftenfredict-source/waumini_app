<?php

namespace App\Console\Commands;

use App\Services\Church\LeadershipStaffAccessService;
use Illuminate\Console\Command;

class SyncLeadershipStaffAccess extends Command
{
    protected $signature = 'church:sync-leadership-access';

    protected $description = 'Sync member portal accounts to staff roles based on active leadership positions';

    public function handle(LeadershipStaffAccessService $leadershipStaffAccessService): int
    {
        $updated = $leadershipStaffAccessService->syncAll();

        $this->info("Updated {$updated} member account(s) to match leadership roles.");

        return self::SUCCESS;
    }
}
