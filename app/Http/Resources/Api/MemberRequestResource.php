<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\MemberRequest */
class MemberRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'assigned_leader' => $this->whenLoaded('assignedLeader', fn () => $this->assignedLeader ? [
                'id' => $this->assignedLeader->id,
                'position_label' => $this->assignedLeader->positionLabel(),
                'name' => $this->assignedLeader->member?->full_name,
            ] : null),
            'response' => $this->response,
            'responded_at' => $this->responded_at?->toIso8601String(),
            'has_certificate' => $this->hasDownloadableCertificate(),
            'request_meta' => $this->request_meta,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
