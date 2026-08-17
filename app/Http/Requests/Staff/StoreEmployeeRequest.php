<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employee = $this->route('employee');

        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id', Rule::unique('employees', 'user_id')->ignore($employee)],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['required', 'string', 'max:150'],
            'employment_type' => ['required', Rule::in(['FULL_TIME', 'PART_TIME', 'CONTRACT', 'INTERN'])],
            'joined_on' => ['required', 'date'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'status' => ['sometimes', Rule::in(['ACTIVE', 'INACTIVE', 'SUSPENDED'])],
            'notes' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
