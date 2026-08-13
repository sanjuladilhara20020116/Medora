<?php

namespace App\Http\Requests\Patients;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $patient = $this->route('patient');

        $patientId = $patient instanceof Patient
            ? $patient->id
            : $patient;

        return [
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'date_of_birth' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'gender' => [
                'required',
                Rule::in([
                    'MALE',
                    'FEMALE',
                    'OTHER',
                    'PREFER_NOT_TO_SAY',
                ]),
            ],

            'blood_group' => [
                'nullable',
                Rule::in([
                    'A+',
                    'A-',
                    'B+',
                    'B-',
                    'AB+',
                    'AB-',
                    'O+',
                    'O-',
                ]),
            ],

            'nic_passport' => [
                'nullable',
                'string',
                'max:50',

                Rule::unique(
                    'patients',
                    'nic_passport'
                )->ignore($patientId),
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'phone' => [
                'required',
                'string',
                'max:20',
            ],

            'alternate_phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'address_line_1' => [
                'required',
                'string',
                'max:255',
            ],

            'address_line_2' => [
                'nullable',
                'string',
                'max:255',
            ],

            'city' => [
                'required',
                'string',
                'max:100',
            ],

            'district' => [
                'nullable',
                'string',
                'max:100',
            ],

            'postal_code' => [
                'nullable',
                'string',
                'max:20',
            ],

            'country' => [
                'nullable',
                'string',
                'max:100',
            ],

            'emergency_contact_name' => [
                'required',
                'string',
                'max:150',
            ],

            'emergency_contact_relation' => [
                'nullable',
                'string',
                'max:100',
            ],

            'emergency_contact_phone' => [
                'required',
                'string',
                'max:20',
            ],

            'allergies' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'chronic_conditions' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}