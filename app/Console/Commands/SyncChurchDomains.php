<?php

namespace App\Console\Commands;

use App\Models\Church;
use App\Services\Owner\ChurchService;
use App\Support\TenantDomain;
use Illuminate\Console\Command;

class SyncChurchDomains extends Command
{
    protected $signature = 'church:sync-domains';

    protected $description = 'Update stored church subdomains to match the current tenant base domain';

    public function handle(ChurchService $churchService): int
    {
        $baseDomain = TenantDomain::base();
        $updated = 0;

        $this->info("Syncing church domains to *.$baseDomain ...");

        Church::query()
            ->with('primaryDomain')
            ->orderBy('id')
            ->each(function (Church $church) use ($churchService, &$updated): void {
                if ($churchService->syncPrimaryDomain($church)) {
                    $updated++;
                    $this->line(" - {$church->slug} -> {$church->tenantDomain()}");
                }
            });

        $this->info("Updated {$updated} church domain(s).");

        return self::SUCCESS;
    }
}
