<?php

namespace App\Console\Commands;

use App\Models\Church;
use App\Services\Church\ChurchSettingsService;
use App\Services\Church\MemberService;
use Illuminate\Console\Command;

class ProcessAgedOutChildren extends Command
{
    protected $signature = 'members:process-aged-out-children {--church= : Church ID to limit processing}';

    protected $description = 'Convert children who have reached the church graduation age into independent members';

    public function handle(MemberService $memberService, ChurchSettingsService $churchSettingsService): int
    {
        $this->info('Processing children who have reached each church graduation age...');

        $churches = Church::query()
            ->when($this->option('church'), fn ($q, $id) => $q->whereKey($id))
            ->get();

        $total = 0;

        foreach ($churches as $church) {
            $age = $churchSettingsService->childGraduationAge($church);
            $count = $memberService->processAgedOutChildren($church);
            $total += $count;

            if ($count > 0) {
                $this->line("Church #{$church->id} (age {$age}+): converted {$count} child(ren).");
            }
        }

        $this->info($total > 0 ? "Done. Converted {$total} child(ren)." : 'No eligible children found.');

        return self::SUCCESS;
    }
}
