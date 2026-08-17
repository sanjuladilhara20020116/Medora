<?php

namespace App\Http\Requests\MedicalRecords;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recorded_at' => ['required', 'date'],
            'chief_complaint' => ['nullable', 'string', 'max:5000'],
            'diagnosis' => ['required', 'string', 'max:5000'],
            'treatment_plan' => ['nullable', 'string', 'max:5000'],
            'clinical_notes' => ['nullable', 'string', 'max:10000'],
            'follow_up_date' => ['nullable', 'date'],
            'follow_up_notes' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
