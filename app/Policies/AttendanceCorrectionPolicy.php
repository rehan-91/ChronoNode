<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\AttendanceCorrection;
use App\Models\User;

class AttendanceCorrectionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AttendanceCorrection $correction): bool
    {
        if ($user->role === Role::HrAdmin) {
            return true;
        }
        
        $attendance = $correction->attendance;

        if ($user->role === Role::Manager) {
            return $attendance && $attendance->user && $attendance->user->manager_id === $user->id;
        }

        return $attendance && $user->id === $attendance->user_id;
    }

    public function create(User $user): bool
    {
        return true; // Users can submit their own corrections
    }

    public function review(User $user, AttendanceCorrection $correction): bool
    {
        if ($user->role === Role::HrAdmin) {
            return true;
        }

        $attendance = $correction->attendance;
        if ($user->role === Role::Manager) {
            return $attendance && $attendance->user && $attendance->user->manager_id === $user->id;
        }

        return false;
    }
}
