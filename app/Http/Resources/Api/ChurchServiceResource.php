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
            'service_type' => $this->service_type?->value,
            'service_date' => $this->service_date?->toDateString(),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'theme' => $this->theme,
            'preacher' => $this->preacherDisplay(),
            'venue' => $this->venue,
            'status' => $this->status?->value,
            'branch' => $this->whenLoaded('branch', fn () => $this->branch ? [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ] : null),
        ];
    }
}
