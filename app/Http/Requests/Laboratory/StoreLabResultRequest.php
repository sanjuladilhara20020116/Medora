<?php

namespace App\Http\Requests\Laboratory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLabResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'result_value' => ['required', 'string', 'max:10000'],
            'unit' => ['nullable', 'string', 'max:100'],
            'reference_range' => ['nullable', 'string', 'max:255'],
            'interpretation' => [
                'required',
                Rule::in(['NORMAL', 'ABNORMAL', 'CRITICAL', 'INCONCLUSIVE']),
            ],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'resulted_at' => ['required', 'date'],
        ];
    }
}
