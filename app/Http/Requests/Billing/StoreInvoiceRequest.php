<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'due_date' => ['nullable', 'date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'tax_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'charge_sources' => ['nullable', 'array', 'max:50'],
            'charge_sources.*.type' => ['required', Rule::in(['APPOINTMENT', 'LAB_REQUEST', 'PRESCRIPTION_DISPENSE', 'ADMISSION'])],
            'charge_sources.*.id' => ['required', 'integer'],
            'manual_items' => ['nullable', 'array', 'max:20'],
            'manual_items.*.item_type' => ['required', Rule::in(['ADMISSION', 'OTHER'])],
            'manual_items.*.description' => ['required', 'string', 'max:255'],
            'manual_items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'manual_items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }
}
