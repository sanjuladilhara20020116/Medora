<?php

namespace App\Http\Requests\Laboratory;

use Illuminate\Foundation\Http\FormRequest;

class StoreLabTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'max:100'],
            'specimen_type' => ['required', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:100'],
            'reference_range' => ['nullable', 'string', 'max:255'],
            'turnaround_hours' => ['nullable', 'integer', 'min:1', 'max:720'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
