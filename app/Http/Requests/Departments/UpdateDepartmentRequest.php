<?php

namespace App\Http\Requests\Departments;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $department =
            $this->route('department');

        $id =
            $department instanceof Department
                ? $department->id
                : $department;

        return [
            'code' => [
                'required',
                'string',
                'max:30',

                Rule::unique(
                    'departments',
                    'code'
                )->ignore($id),
            ],

            'name' => [
                'required',
                'string',
                'max:150',

                Rule::unique(
                    'departments',
                    'name'
                )->ignore($id),
            ],

            'description' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}