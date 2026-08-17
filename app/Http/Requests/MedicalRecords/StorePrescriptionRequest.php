<?php

namespace App\Http\Requests\MedicalRecords;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'issued_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.medicine_name' => ['required', 'string', 'max:200'],
            'items.*.dosage' => ['required', 'string', 'max:100'],
            'items.*.frequency' => ['required', 'string', 'max:100'],
            'items.*.duration_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0.01', 'max:999999.99'],
            'items.*.instructions' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
