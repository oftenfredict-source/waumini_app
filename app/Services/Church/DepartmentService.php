<?php

namespace App\Services\Church;

use App\Models\Church;
use App\Models\Department;
use App\Models\Member;
use App\Models\MemberDependant;

class DepartmentService
{
    public function create(Church $church, array $data): Department
    {
        $data['church_id'] = $church->id;

        $trashed = Department::onlyTrashed()
            ->forChurch($church->id)
            ->where('name', $data['name'])
            ->first();

        if ($trashed) {
            $trashed->restore();
            $trashed->update([
                'description' => $data['description'] ?? null,
                'head_id' => $data['head_id'] ?? null,
                'status' => $data['status'],
            ]);
            $department = $trashed->fresh();
        } else {
            $department = Department::create($data);
        }

        if (! empty($data['head_id'])) {
            $this->ensureHeadIsMember($department, (int) $data['head_id']);
        }

        return $department->fresh(['head', 'members']);
    }

    public function update(Department $department, array $data): Department
    {
        if (! empty($data['name']) && $data['name'] !== $department->name) {
            $this->forceDeleteTrashedNameConflict($department->church_id, $data['name'], $department->id);
        }

        $department->update($data);

        if (array_key_exists('head_id', $data) && $data['head_id']) {
            $this->ensureHeadIsMember($department, (int) $data['head_id']);
        }

        return $department->fresh(['head', 'members']);
    }

    public function assignHead(Department $department, ?int $headId): Department
    {
        $department->update(['head_id' => $headId]);

        if ($headId) {
            $this->ensureHeadIsMember($department, $headId);
        }

        return $department->fresh(['head', 'members']);
    }

    public function attachMembers(Department $department, array $memberIds, bool $autoAssigned = false): int
    {
        $attached = 0;

        foreach ($memberIds as $memberId) {
            $memberId = (int) $memberId;

            if ($department->members()->where('member_id', $memberId)->exists()) {
                continue;
            }

            $department->members()->attach($memberId, [
                'role' => $department->head_id === $memberId ? 'head' : 'member',
                'auto_assigned' => $autoAssigned,
            ]);
            $attached++;
        }

        return $attached;
    }

    public function attachDependants(Department $department, array $dependantIds, bool $autoAssigned = true): int
    {
        $attached = 0;

        foreach ($dependantIds as $dependantId) {
            $dependantId = (int) $dependantId;

            if ($department->dependants()->where('member_dependant_id', $dependantId)->exists()) {
                continue;
            }

            $department->dependants()->attach($dependantId, [
                'auto_assigned' => $autoAssigned,
            ]);
            $attached++;
        }

        return $attached;
    }

    public function removeMember(Department $department, Member $member): void
    {
        $department->members()->detach($member->id);

        if ($department->head_id === $member->id) {
            $department->update(['head_id' => null]);
        }
    }

    public function removeDependant(Department $department, MemberDependant $dependant): void
    {
        $department->dependants()->detach($dependant->id);
    }

    public function delete(Department $department): void
    {
        $department->members()->detach();
        $department->dependants()->detach();
        $department->delete();
    }

    private function forceDeleteTrashedNameConflict(int $churchId, string $name, ?int $exceptId = null): void
    {
        $query = Department::onlyTrashed()
            ->forChurch($churchId)
            ->where('name', $name);

        if ($exceptId) {
            $query->whereKeyNot($exceptId);
        }

        $query->forceDelete();
    }

    private function ensureHeadIsMember(Department $department, int $headId): void
    {
        $department->members()->syncWithoutDetaching([
            $headId => [
                'role' => 'head',
                'auto_assigned' => false,
            ],
        ]);
    }
}
