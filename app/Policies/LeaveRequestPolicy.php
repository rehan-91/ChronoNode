<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->role === Role::HrAdmin) {
            return true;
        }

        if ($user->role === Role::Manager) {
            $targetUser = $leaveRequest->user;
            return $targetUser && $targetUser->manager_id === $user->id;
        }

        return $user->id === $leaveRequest->user_id;
    }

    public function create(User $user): bool
    {
        return true; // Any user can submit a leave request
    }

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        // Users can update their own request if it's pending
        return $user->id === $leaveRequest->user_id && $leaveRequest->status->value === 'pending';
    }

    public function review(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->role === Role::HrAdmin) {
            return true;
        }

        if ($user->role === Role::Manager) {
            $targetUser = $leaveRequest->user;
            return $targetUser && $targetUser->manager_id === $user->id;
        }

        return false;
    }
}
