<?php

declare(strict_types=1);

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class GenerateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role->value === 'hr_admin' || $this->user()->role->value === 'super_admin';
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:daily_log,monthly_master,late_patterns,overtime_aggregates'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ];
    }
}
