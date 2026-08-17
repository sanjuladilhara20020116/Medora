<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MedicalRecord extends Model
{
    protected $fillable = [
        'record_code',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'recorded_at',
        'chief_complaint',
        'diagnosis',
        'treatment_plan',
        'clinical_notes',
        'follow_up_date',
        'follow_up_notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'follow_up_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function prescription(): HasOne
    {
        return $this->hasOne(Prescription::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(MedicalReport::class);
    }
}
