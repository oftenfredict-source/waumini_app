<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\BereavementContribution */
class BereavementContributionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'has_contributed' => (bool) $this->has_contributed,
            'amount' => $this->amount === null ? null : (float) $this->amount,
            'contribution_date' => $this->contribution_date?->toDateString(),
            'contribution_type' => $this->contribution_type?->value,
            'contribution_type_label' => $this->contribution_type?->label(),
            'payment_method' => $this->payment_method?->value,
            'payment_method_label' => $this->payment_method?->label(),
            'reference_number' => $this->reference_number,
            'notes' => $this->notes,
            'member' => $this->whenLoaded('member', fn () => $this->member ? [
                'id' => $this->member->id,
                'full_name' => $this->member->full_name,
                'member_number' => $this->member->member_number,
                'envelope_number' => $this->member->envelope_number,
                'phone_number' => $this->member->phone_number,
            ] : null),
        ];
    }
}
