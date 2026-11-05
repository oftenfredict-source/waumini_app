<?php

namespace App\Console\Commands;

use App\Services\Owner\TrialExpiryService;
use Illuminate\Console\Command;

class ProcessTrialExpiry extends Command
{
    protected $signature = 'trials:process-expiry';

    protected $description = 'Send 4-day trial expiry SMS reminders and lock churches whose free trial has ended';

    public function handle(TrialExpiryService $service): int
    {
        $this->info('Processing trial expiry reminders and locks...');

        $result = $service->processDue();

        $this->line("Reminders sent: {$result['reminded']}");
        $this->line("Trials expired/locked: {$result['expired']}");
        $this->line("Expiry SMS sent: {$result['expiry_notified']}");

        $this->info('Done.');

        return self::SUCCESS;
    }
}
