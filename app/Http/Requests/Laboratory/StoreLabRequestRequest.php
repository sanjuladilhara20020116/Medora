<?php

namespace App\Http\Requests\Laboratory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLabRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
            'lab_test_id' => ['required', 'integer', 'exists:lab_tests,id'],
            'medical_record_id' => ['nullable', 'integer', 'exists:medical_records,id'],
            'priority' => ['required', Rule::in(['NORMAL', 'URGENT'])],
            'clinical_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
