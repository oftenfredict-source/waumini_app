<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ChurchService */
class ChurchServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->displayTitle(),
            'custom_title' => $this->title,
            'service_type' => $this->service_type?->value,
            'service_type_label' => $this->service_type?->label(),
            'service_date' => $this->service_date?->toDateString(),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'theme' => $this->theme,
            'preacher' => $this->preacherDisplay(),
            'venue' => $this->venue,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'branch' => $this->whenLoaded('branch', fn () => $this->branch ? [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ] : null),
        ];
    }
}
