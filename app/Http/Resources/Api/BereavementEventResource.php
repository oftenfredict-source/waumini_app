<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\BereavementEvent */
class BereavementEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'deceased_name' => $this->deceased_name,
            'family_details' => $this->family_details,
            'related_departments' => $this->related_departments,
            'incident_date' => $this->incident_date?->toDateString(),
            'contribution_start_date' => $this->contribution_start_date?->toDateString(),
            'contribution_end_date' => $this->contribution_end_date?->toDateString(),
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'notes' => $this->notes,
            'fund_usage' => $this->fund_usage,
            'closed_at' => $this->closed_at?->toIso8601String(),
            'days_remaining' => $this->daysRemaining(),
            'is_expired' => $this->isExpired(),
            'total_contributions' => $this->totalContributions(),
            'contributors_count' => $this->contributorsCount(),
            'pending_count' => $this->pendingCount(),
            'affected_member' => $this->whenLoaded('affectedMember', fn () => $this->affectedMember ? [
                'id' => $this->affectedMember->id,
                'full_name' => $this->affectedMember->full_name,
                'member_number' => $this->affectedMember->member_number,
                'phone_number' => $this->affectedMember->phone_number,
            ] : null),
            'can_update' => $request->user()?->can('update', $this->resource) ?? false,
            'can_manage_contributions' => $request->user()?->can('manageContributions', $this->resource) ?? false,
        ];
    }
}
