<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->role === Role::HrAdmin) {
            return true;
        }

        if ($user->role === Role::Manager) {
            $targetUser = $attendance->user;
            return $targetUser && $targetUser->manager_id === $user->id;
        }

        return $user->id === $attendance->user_id;
    }

    public function create(User $user): bool
    {
        // Users can create their own attendance (check-in/out)
        return true;
    }

    public function update(User $user, Attendance $attendance): bool
    {
        // Only HR admins can directly update records
        return $user->role === Role::HrAdmin;
    }
}
