<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Department;

class DepartmentService
{
    public function createDepartment(array $data): Department
    {
        return Department::create($data);
    }

    public function updateDepartment(Department $department, array $data): bool
    {
        return $department->update($data);
    }

    public function toggleStatus(Department $department): bool
    {
        return $department->update(['is_active' => !$department->is_active]);
    }
}
