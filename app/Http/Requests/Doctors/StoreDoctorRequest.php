<?php

namespace App\Http\Requests\Doctors;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'username' => [
                'required',
                'string',
                'max:100',
                'unique:users,username',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'phone' => [
                'required',
                'string',
                'max:20',
            ],

            'registration_number' => [
                'required',
                'string',
                'max:100',
                'unique:doctors,registration_number',
            ],

            'designation' => [
                'nullable',
                'string',
                'max:150',
            ],

            'specialization' => [
                'nullable',
                'string',
                'max:150',
            ],

            'qualifications' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'experience_years' => [
                'nullable',
                'integer',
                'min:0',
                'max:80',
            ],

            'consultation_fee' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'room_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'biography' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'joined_at' => [
                'nullable',
                'date',
            ],

            'department_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'department_ids.*' => [
                'integer',
                Rule::exists(
                    'departments',
                    'id'
                ),
            ],

            'primary_department_id' => [
                'required',
                'integer',
                Rule::exists(
                    'departments',
                    'id'
                ),
            ],
        ];
    }
}
