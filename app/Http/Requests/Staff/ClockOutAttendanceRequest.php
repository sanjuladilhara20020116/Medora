<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class ClockOutAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_out' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
