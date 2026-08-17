<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'attendance_date' => ['required', 'date'],
            'clock_in' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['PRESENT', 'LATE', 'ABSENT', 'ON_LEAVE'])],
            'notes' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
