<?php

namespace App\Services\Church;

use App\Enums\FinancialApprovalStatus;
use App\Models\Church;
use App\Models\Member;
use App\Models\Tithe;
use App\Models\User;

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
