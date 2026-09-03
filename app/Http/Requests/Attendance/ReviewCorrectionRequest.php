<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class ReviewCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('correction'));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:approved,rejected'],
            'reason' => ['required', 'string', 'max:1000'],
            'edited_check_in' => ['nullable', 'date'],
            'edited_check_out' => ['nullable', 'date', 'after_or_equal:edited_check_in'],
        ];
    }
}
