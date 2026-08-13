<?php

namespace App\Http\Requests\Doctors;

use App\Models\Doctor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Doctor|null $doctor */
        $doctor =
            $this->route('doctor');

        $doctorId =
            $doctor?->id;

        $userId =
            $doctor?->user_id;

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

                Rule::unique(
                    'users',
                    'username'
                )->ignore($userId),
            ],

            'email' => [
                'required',
                'email',
                'max:255',

                Rule::unique(
                    'users',
                    'email'
                )->ignore($userId),
            ],

            'phone' => [
                'required',
                'string',
                'max:20',
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'registration_number' => [
                'required',
                'string',
                'max:100',

                Rule::unique(
                    'doctors',
                    'registration_number'
                )->ignore($doctorId),
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

            'is_available' => [
                'sometimes',
                'boolean',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

            'department_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'department_ids.*' => [
                'integer',
                'exists:departments,id',
            ],

            'primary_department_id' => [
                'required',
                'integer',
                'exists:departments,id',
            ],
        ];
    }
}