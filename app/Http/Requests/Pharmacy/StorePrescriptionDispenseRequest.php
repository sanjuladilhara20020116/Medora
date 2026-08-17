<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionDispenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:3000'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.prescription_item_id' => ['required', 'integer', 'distinct', 'exists:prescription_items,id'],
            'items.*.medicine_stock_id' => ['required', 'integer', 'exists:medicine_stocks,id'],
            'items.*.quantity_dispensed' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ];
    }
}
