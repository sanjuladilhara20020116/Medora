<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admission extends Model
{
    protected $fillable = [
        'admission_code', 'patient_id', 'attending_doctor_id', 'admitted_on',
        'discharged_on', 'room_number', 'status', 'daily_rate', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'admitted_on' => 'date',
            'discharged_on' => 'date',
            'daily_rate' => 'decimal:2',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function attendingDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'attending_doctor_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
