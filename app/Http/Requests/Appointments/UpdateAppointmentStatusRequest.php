<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',

                Rule::in([
                    'CHECKED_IN',
                    'IN_PROGRESS',
                    'COMPLETED',
                    'CANCELLED',
                    'NO_SHOW',
                ]),
            ],

            'cancellation_reason' => [
                'nullable',
                'required_if:status,CANCELLED',
                'string',
                'max:2000',
            ],
        ];
    }
}