<?php

declare(strict_types=1);

namespace App\Http\Requests\Employee;

use App\Enums\EmployeeStatus;
use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('employee'));
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($employeeId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'employee_code' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($employeeId)],
            'department_id' => ['nullable', 'exists:departments,id'],
            'office_id' => ['nullable', 'exists:offices,id'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'role' => ['required', Rule::enum(Role::class)],
            'status' => ['required', Rule::enum(EmployeeStatus::class)],
            'joining_date' => ['nullable', 'date'],
        ];
    }
}
