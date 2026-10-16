<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\SpecialEvent */
class SpecialEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category?->value,
            'category_label' => $this->categoryLabel(),
            'category_other' => $this->category_other,
            'event_date' => $this->event_date?->toDateString(),
            'start_time' => $this->clock($this->start_time),
            'end_time' => $this->clock($this->end_time),
            'speaker' => $this->speaker,
            'venue' => $this->venue,
            'budget_amount' => $this->budget_amount !== null ? (float) $this->budget_amount : null,
            'expected_attendance' => $this->expected_attendance,
            'guests_count' => $this->guests_count,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'description' => $this->description,
            'notes' => $this->notes,
            'branch_id' => $this->branch_id,
            'created_by_name' => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'can_update' => $user?->can('update', $this->resource) ?? false,
            'can_delete' => $user?->can('delete', $this->resource) ?? false,
            'branch' => $this->whenLoaded('branch', fn () => $this->branch ? [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ] : null),
        ];
    }

    private function clock(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_object($value) && method_exists($value, 'format')) {
            return $value->format('H:i');
        }

        $text = substr(trim((string) $value), 0, 5);

        return $text === '' ? null : $text;
    }
}
