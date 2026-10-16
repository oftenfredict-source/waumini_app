<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Department */
class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'members_count' => $this->when(
                isset($this->members_count) || $this->relationLoaded('members'),
                fn () => $this->members_count ?? $this->members->count(),
            ),
            'dependants_count' => $this->when(
                isset($this->dependants_count) || $this->relationLoaded('dependants'),
                fn () => $this->dependants_count ?? $this->dependants->count(),
            ),
            'head' => $this->whenLoaded('head', fn () => $this->head ? [
                'id' => $this->head->id,
                'full_name' => $this->head->full_name,
                'member_number' => $this->head->member_number,
                'phone_number' => $this->head->phone_number,
                'role' => 'chairman',
            ] : null),
            'branch' => $this->whenLoaded('branch', fn () => $this->branch ? [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ] : null),
            'members' => $this->whenLoaded('members', fn () => $this->members
                ->sortBy('full_name')
                ->map(fn ($member) => [
                    'id' => $member->id,
                    'full_name' => $member->full_name,
                    'member_number' => $member->member_number,
                    'phone_number' => $member->phone_number,
                    'role' => $this->normalizeRole($member->pivot->role ?? null, (int) $member->id),
                ])
                ->values()
                ->all()),
            'dependants' => $this->whenLoaded('dependants', fn () => $this->dependants
                ->sortBy('full_name')
                ->map(fn ($child) => [
                    'id' => $child->id,
                    'full_name' => $child->full_name,
                    'age' => $child->age(),
                    'guardian_name' => $child->member?->full_name ?? $child->guardian_full_name,
                ])
                ->values()
                ->all()),
            'can_update' => $request->user()?->can('update', $this->resource) ?? false,
            'can_delete' => $request->user()?->can('delete', $this->resource) ?? false,
        ];
    }

    private function normalizeRole(?string $role, int $memberId): string
    {
        $normalized = strtolower(trim((string) $role));
        if (in_array($normalized, ['head', 'chairman', 'chairperson', 'chair'], true)
            || (int) $this->head_id === $memberId) {
            return 'chairman';
        }
        if (in_array($normalized, ['secretary', 'treasurer'], true)) {
            return $normalized;
        }

        return $normalized !== '' ? $normalized : 'member';
    }
}
