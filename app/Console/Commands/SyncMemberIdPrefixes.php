<?php

namespace App\Console\Commands;

use App\Models\Church;
use App\Services\Church\MemberIdPrefixService;
use Illuminate\Console\Command;

class SyncMemberIdPrefixes extends Command
{
    protected $signature = 'church:sync-member-id-prefixes';

    protected $description = 'Generate unique member ID prefixes for churches that do not have one yet';

    public function handle(MemberIdPrefixService $prefixService): int
    {
        $updated = 0;

        Church::query()->orderBy('id')->each(function (Church $church) use ($prefixService, &$updated): void {
            $current = strtoupper((string) data_get($church->settings, 'member_id_prefix', ''));

            if ($current !== '' && $current !== 'WL' && ! $prefixService->isUsed($current, $church->id)) {
                return;
            }

            $prefix = $prefixService->generateUnique($church->name, $church->id);
            $settings = array_merge($church->settings ?? [], ['member_id_prefix' => $prefix]);
            $church->update(['settings' => $settings]);
            $updated++;
            $this->line(" - {$church->name}: {$prefix} (e.g. {$prefixService->exampleMemberId($prefix)})");
        });

        $this->info("Updated {$updated} church prefix(es).");

        return self::SUCCESS;
    }
}
