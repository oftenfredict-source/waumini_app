<?php

namespace App\Services\Church;

use App\Enums\FinancialApprovalStatus;
use App\Enums\OfferingContributionType;
use App\Enums\OfferingType;
use App\Models\Church;
use App\Models\Member;
use App\Models\Offering;
use App\Models\User;

class OfferingService
{
    public function create(Church $church, array $data, ?User $recorder = null): Offering
    {
        $data = $this->normalizeContributionFields($data);
        $data['church_id'] = $church->id;

        if (! empty($data['member_id'])) {
            $data['branch_id'] = Member::query()->whereKey($data['member_id'])->value('branch_id');
        }

        $data['recorded_by'] = $recorder?->id;
        $data['approval_status'] = FinancialApprovalStatus::Pending;

        return Offering::create($this->normalizeTypeFields($data));
    }

    public function update(Offering $offering, array $data): Offering
    {
        unset($data['approval_status'], $data['approved_by'], $data['approved_at'], $data['church_id']);

        $data = $this->normalizeContributionFields($data);

        if (! empty($data['member_id'])) {
            $data['branch_id'] = Member::query()->whereKey($data['member_id'])->value('branch_id');
        } else {
            $data['branch_id'] = null;
        }

        $offering->update($this->normalizeTypeFields($data));

        return $offering->fresh();
    }

    public function delete(Offering $offering): void
    {
        $offering->delete();
    }

    private function normalizeContributionFields(array $data): array
    {
        $contributionType = $data['contribution_type'] ?? null;
        unset($data['contribution_type']);

        if ($contributionType === OfferingContributionType::Member->value) {
            $data['church_service_id'] = null;
            $data['member_id'] = $data['member_id'] ?? null;
        } elseif ($contributionType === OfferingContributionType::General->value) {
            $data['member_id'] = null;
            $data['church_service_id'] = $data['church_service_id'] ?? null;
        } elseif (empty($data['member_id'])) {
            $data['member_id'] = null;
        }

        return $data;
    }

    private function normalizeTypeFields(array $data): array
    {
        if (($data['offering_type'] ?? null) !== OfferingType::Other->value) {
            $data['offering_type_other'] = null;
        }

        return $data;
    }
}
