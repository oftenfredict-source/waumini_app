<?php

namespace App\Console\Commands;

use App\Models\Church;
use App\Services\Church\MemberService;
use Illuminate\Console\Command;

class ProcessAgedOutChildren extends Command
{
    protected $signature = 'members:process-aged-out-children {--church= : Church ID to limit processing}';

    protected $description = 'Convert children who have reached the independence age into independent members';

    public function handle(MemberService $memberService): int
    {
        $age = config('membership.child_independence_age', 21);
        $this->info("Processing children aged {$age} and above...");

        $churches = Church::query()
            ->when($this->option('church'), fn ($q, $id) => $q->whereKey($id))
            ->get();

        $total = 0;

        foreach ($churches as $church) {
            $count = $memberService->processAgedOutChildren($church);
            $total += $count;

            if ($count > 0) {
                $this->line("Church #{$church->id}: converted {$count} child(ren).");
            }
        }

        $this->info($total > 0 ? "Done. Converted {$total} child(ren)." : 'No eligible children found.');

        return self::SUCCESS;
    }
}
