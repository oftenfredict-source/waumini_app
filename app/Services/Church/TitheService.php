<?php

namespace App\Services\Church;

use App\Enums\FinancialApprovalStatus;
use App\Models\Church;
use App\Models\Member;
use App\Models\Tithe;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TitheService
{
    public function create(Church $church, array $data, ?User $recorder = null): Tithe
    {
        $data['church_id'] = $church->id;
        $data['branch_id'] = Member::query()->whereKey($data['member_id'] ?? null)->value('branch_id');
        $data['recorded_by'] = $recorder?->id;
        $data['approval_status'] = FinancialApprovalStatus::Pending;

        return Tithe::create($data);
    }

    /**
     * @param  list<array<string, mixed>>  $entries
     * @return Collection<int, Tithe>
     */
    public function createMany(Church $church, array $entries, ?User $recorder = null): Collection
    {
        return DB::transaction(function () use ($church, $entries, $recorder) {
            return collect($entries)
                ->map(fn (array $data) => $this->create($church, $data, $recorder))
                ->values();
        });
    }

    public function update(Tithe $tithe, array $data): Tithe
    {
        unset($data['approval_status'], $data['approved_by'], $data['approved_at'], $data['church_id']);

        $tithe->update($data);

        return $tithe->fresh();
    }

    public function delete(Tithe $tithe): void
    {
        $tithe->delete();
    }
}
