<?php

namespace App\Http\Requests\Laboratory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CollectSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sample_identifier' => ['required', 'string', 'max:100'],
            'specimen_condition' => [
                'required',
                Rule::in(['ACCEPTABLE', 'HEMOLYZED', 'CLOTTED', 'INSUFFICIENT', 'OTHER']),
            ],
            'sample_notes' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
