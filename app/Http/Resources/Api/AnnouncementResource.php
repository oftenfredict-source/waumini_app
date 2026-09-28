<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Announcement */
class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => str($this->content ? strip_tags($this->content) : '')->limit(120)->toString(),
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'is_pinned' => (bool) $this->is_pinned,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'department' => $this->whenLoaded('department', fn () => $this->department ? [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
