<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => [
                'required',
                'integer',
                'exists:patients,id',
            ],

            'doctor_id' => [
                'required',
                'integer',
                'exists:doctors,id',
            ],

            'department_id' => [
                'required',
                'integer',
                'exists:departments,id',
            ],

            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'appointment_type' => [
                'required',

                Rule::in([
                    'CONSULTATION',
                    'FOLLOW_UP',
                    'PROCEDURE',
                    'OTHER',
                ]),
            ],

            'priority' => [
                'required',

                Rule::in([
                    'NORMAL',
                    'URGENT',
                ]),
            ],

            'reason' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ];
    }
}