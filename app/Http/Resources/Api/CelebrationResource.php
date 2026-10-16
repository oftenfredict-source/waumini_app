<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Celebration */
class CelebrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'display_label' => $this->displayLabel(),
            'celebration_type' => $this->celebration_type?->value,
            'celebration_type_label' => $this->celebration_type?->label(),
            'celebration_date' => $this->celebration_date?->toDateString(),
            'original_date' => $this->original_date?->toDateString(),
            'wedding_type' => $this->wedding_type?->value,
            'wedding_type_label' => $this->wedding_type?->label(),
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'source' => $this->source?->value,
            'source_label' => $this->source?->label(),
            'notes' => $this->notes,
            'venue' => $this->venue,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'special_guest' => $this->special_guest,
            'days_until' => $this->daysUntil(),
            'years_count' => $this->yearsCount(),
            'spouse_name' => $this->spouseName(),
            'member' => $this->whenLoaded('member', fn () => $this->member ? [
                'id' => $this->member->id,
                'full_name' => $this->member->full_name,
                'member_number' => $this->member->member_number,
                'spouse_full_name' => $this->spouseName(),
            ] : null),
        ];
    }
}
