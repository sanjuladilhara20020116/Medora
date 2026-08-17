<?php

namespace App\Http\Requests\Laboratory;

class UpdateLabTestRequest extends StoreLabTestRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'is_active' => ['required', 'boolean'],
        ];
    }
}
