<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Leader */
class LeaderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position?->value,
            'position_label' => $this->positionLabel(),
            'member' => $this->whenLoaded('member', fn () => $this->member ? [
                'id' => $this->member->id,
                'full_name' => $this->member->full_name,
                'member_number' => $this->member->member_number,
                'phone_number' => $this->member->phone_number,
                'profile_picture_url' => $this->member->profilePictureUrl(),
            ] : null),
            'appointment_date' => $this->appointment_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
        ];
    }
}
