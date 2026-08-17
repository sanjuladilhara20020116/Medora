<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicineStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'batch_number' => ['required', 'string', 'max:100'],
            'expiry_date' => ['required', 'date', 'after:today'],
            'received_date' => ['required', 'date', 'before_or_equal:today'],
            'quantity_received' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'unit_cost' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'supplier' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
