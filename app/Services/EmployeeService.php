<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeService
{
    public function createEmployee(array $data): User
    {
        // Auto-generate password if not provided
        $password = $data['password'] ?? Str::random(16);
        $data['password'] = Hash::make($password);

        return User::create($data);
    }

    public function updateEmployee(User $employee, array $data): bool
    {
        if (isset($data['password']) && filled($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $employee->update($data);
    }

    public function deactivateEmployee(User $employee): bool
    {
        return $employee->update(['status' => \App\Enums\EmployeeStatus::Inactive->value]);
    }
    
    public function reactivateEmployee(User $employee): bool
    {
        return $employee->update(['status' => \App\Enums\EmployeeStatus::Active->value]);
    }
}
