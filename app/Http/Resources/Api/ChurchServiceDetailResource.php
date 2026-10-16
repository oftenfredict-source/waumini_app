<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class ChurchServiceDetailResource extends ChurchServiceResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'custom_title' => $this->title,
            'notes' => $this->notes,
            'branch_id' => $this->branch_id,
            'preacher_name' => $this->preacher ?: $this->preacherMember?->full_name,
            'preacher_type' => $this->preacher_type?->value,
            'preacher_member_id' => $this->preacher_member_id,
            'preacher_guest_name' => $this->preacher_type?->value === 'guest' ? $this->preacher : null,
            'preacher_guest_phone' => $this->preacher_phone,
            'coordinator' => $this->coordinatorDisplay(),
            'coordinator_name' => $this->coordinator_name ?: $this->coordinatorMember?->full_name,
            'coordinator_type' => $this->coordinator_type?->value,
            'coordinator_member_id' => $this->coordinator_member_id,
            'coordinator_guest_name' => $this->coordinator_type?->value === 'guest' ? $this->coordinator_name : null,
            'coordinator_guest_phone' => $this->coordinator_phone,
        ]);
    }
}
