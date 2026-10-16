<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\AttendanceRecord */
class AttendanceRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'source_type' => $this->source_type?->value,
            'source_type_label' => $this->source_type?->label(),
            'source_id' => $this->source_id,
            'attended_at' => $this->attended_at?->toIso8601String(),
            'notes' => $this->notes,
            'member' => $this->whenLoaded('member', fn () => $this->member ? [
                'id' => $this->member->id,
                'full_name' => $this->member->full_name,
                'member_number' => $this->member->member_number,
            ] : null),
            'dependant' => $this->whenLoaded('dependant', fn () => $this->dependant ? [
                'id' => $this->dependant->id,
                'full_name' => $this->dependant->full_name,
            ] : null),
            'branch' => $this->whenLoaded('branch', fn () => $this->branch ? [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ] : null),
        ];
    }
}
