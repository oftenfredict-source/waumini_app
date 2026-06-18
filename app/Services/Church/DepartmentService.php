<?php

namespace App\Services\Church;

use App\Models\Church;
use App\Models\Department;
use App\Models\Member;

class DepartmentService
{
    public function create(Church $church, array $data): Department
    {
        $data['church_id'] = $church->id;

        $department = Department::create($data);

        if (! empty($data['head_id'])) {
            $this->ensureHeadIsMember($department, (int) $data['head_id']);
        }

        return $department->fresh(['head', 'members']);
    }

    public function update(Department $department, array $data): Department
    {
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

    public function attachMembers(Department $department, array $memberIds): int
    {
        $attached = 0;

        foreach ($memberIds as $memberId) {
            $memberId = (int) $memberId;

            if ($department->members()->where('member_id', $memberId)->exists()) {
                continue;
            }

            $department->members()->attach($memberId, [
                'role' => $department->head_id === $memberId ? 'head' : 'member',
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

    public function delete(Department $department): void
    {
        $department->members()->detach();
        $department->delete();
    }

    private function ensureHeadIsMember(Department $department, int $headId): void
    {
        $department->members()->syncWithoutDetaching([
            $headId => ['role' => 'head'],
        ]);
    }
}
