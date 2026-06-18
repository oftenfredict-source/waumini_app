<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('attendance.view');
    }

    public function view(User $user): bool
    {
        return $user->isChurchUser() && $user->can('attendance.view');
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('attendance.manage');
    }

    public function update(User $user): bool
    {
        return $user->isChurchUser() && $user->can('attendance.manage');
    }

    public function delete(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->isChurchUser()
            && $user->can('attendance.manage')
            && $attendanceRecord->church_id === $user->church_id;
    }
}
