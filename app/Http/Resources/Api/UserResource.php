<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'user_type' => $this->user_type?->value,
            'status' => $this->status?->value,
            'church_id' => $this->church_id,
            'branch_id' => $this->branch_id,
            'member_id' => $this->member_id,
            'church_role' => $this->churchRoleLabel(),
            'roles' => $this->getRoleNames()->values()->all(),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
        ];
    }
}
