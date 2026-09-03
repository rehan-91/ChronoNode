<?php

declare(strict_types=1);

namespace App\Http\Requests\Employee;

use App\Enums\EmployeeStatus;
use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\User::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'employee_code' => ['nullable', 'string', 'max:50', 'unique:users,employee_code'],
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
