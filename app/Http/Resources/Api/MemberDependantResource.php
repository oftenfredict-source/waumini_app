<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\MemberDependant */
class MemberDependantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'relationship' => $this->relationship?->value,
            'relationship_label' => $this->relationship?->label(),
            'is_baptized' => (bool) $this->is_baptized,
        ];
    }
}
