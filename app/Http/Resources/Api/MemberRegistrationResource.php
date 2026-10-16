<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\MemberRegistrationApplication */
class MemberRegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'application_number' => $this->application_number,
            'full_name' => $this->full_name,
            'phone_number' => $this->phone_number,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'assigned_envelope_number' => $this->assigned_envelope_number,
            'profile_picture_url' => $this->profilePictureUrl(),
            'registration_data' => $this->registration_data,
            'dependants_data' => $this->dependants_data,
            'member_id' => $this->member_id,
            'rejection_reason' => $this->rejection_reason,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'branch' => $this->whenLoaded('branch', fn () => $this->branch ? [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
