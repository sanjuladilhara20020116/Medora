<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorSchedule extends Model
{
    protected $fillable = [
        'doctor_id',
        'department_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration_minutes',
        'max_appointments',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'slot_duration_minutes' => 'integer',
            'max_appointments' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}