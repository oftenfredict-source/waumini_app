<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member */
class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'church_id' => $this->church_id,
            'branch_id' => $this->branch_id,
            'member_number' => $this->member_number,
            'envelope_number' => $this->envelope_number,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'member_type' => $this->enumValue($this->member_type),
            'membership_type' => $this->enumValue($this->membership_type),
            'status' => $this->enumValue($this->status),
            'membership_date' => $this->membership_date?->toDateString(),
            'membership_expires_at' => $this->membership_expires_at?->toDateString(),
            'profile_picture_url' => $this->profilePictureUrl(),
            'branch' => $this->whenLoaded('branch', fn () => $this->branch ? [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ] : null),
        ];
    }

    protected function enumValue(mixed $value): ?string
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        return $value !== null ? (string) $value : null;
    }
}
