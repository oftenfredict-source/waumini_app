<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class ChurchServiceDetailResource extends ChurchServiceResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'service_type_label' => $this->service_type?->label(),
            'status_label' => $this->status?->label(),
            'custom_title' => $this->title,
            'coordinator' => $this->coordinatorDisplay(),
            'notes' => $this->notes,
        ]);
    }
}
