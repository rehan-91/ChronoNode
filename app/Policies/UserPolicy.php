<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::HrAdmin, Role::Manager], true);
    }

    public function view(User $user, User $targetUser): bool
    {
        if ($user->role === Role::HrAdmin) {
            return true;
        }

        if ($user->role === Role::Manager && $targetUser->manager_id === $user->id) {
            return true;
        }

        return $user->id === $targetUser->id;
    }

    public function create(User $user): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function update(User $user, User $targetUser): bool
    {
        if ($user->role === Role::HrAdmin) {
            return true;
        }
        
        return $user->id === $targetUser->id;
    }

    public function delete(User $user, User $targetUser): bool
    {
        return $user->role === Role::HrAdmin && $user->id !== $targetUser->id;
    }
}
