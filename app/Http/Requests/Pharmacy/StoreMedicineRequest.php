<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medicine_category_id' => ['nullable', 'integer', 'exists:medicine_categories,id'],
            'name' => ['required', 'string', 'max:200'],
            'generic_name' => ['nullable', 'string', 'max:200'],
            'manufacturer' => ['nullable', 'string', 'max:200'],
            'dosage_form' => ['required', 'string', 'max:100'],
            'strength' => ['nullable', 'string', 'max:100'],
            'reorder_level' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'description' => ['nullable', 'string', 'max:3000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
