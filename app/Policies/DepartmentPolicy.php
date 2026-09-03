<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Everyone can see departments typically
    }

    public function view(User $user, Department $department): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function update(User $user, Department $department): bool
    {
        return $user->role === Role::HrAdmin;
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->role === Role::HrAdmin;
    }
}
