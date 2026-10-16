<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ChurchBranch */
class ChurchBranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'is_headquarters' => (bool) $this->is_headquarters,
            'is_active' => (bool) $this->is_active,
            'pastor_name' => $this->pastor_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'city' => $this->city,
            'logo_url' => $this->logoUrl(),
            'members_count' => (int) ($this->members_count ?? 0),
            'leaders_count' => (int) ($this->leaders_count ?? 0),
            'can_update' => $request->user()?->can('update', $this->resource) ?? false,
        ];
    }
}
