<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Determine whether the user can view any attendance records.
     */
    public function viewAny(User $user): bool
    {
        return true; // Authenticated users can view their own dashboard
    }

    /**
     * Determine whether the user can view the specific attendance record.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        // Users can view their own attendance
        if ($user->id === $attendance->user_id) {
            return true;
        }

        // Managers can view team members in same department or line manager relation
        if ($user->isManager() || $user->isHr() || $user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can check in.
     */
    public function checkIn(User $user, int $targetUserId): bool
    {
        // Users can only check in for themselves
        return $user->id === $targetUserId;
    }

    /**
     * Determine whether the user can check out.
     */
    public function checkOut(User $user, Attendance $attendance): bool
    {
        // Users can only check out their own attendance
        return $user->id === $attendance->user_id && $attendance->isWorking();
    }

    /**
     * Determine whether the user can manage administrative attendance tasks.
     */
    public function manage(User $user): bool
    {
        return $user->isAdmin() || $user->isHr() || $user->isManager();
    }
}
