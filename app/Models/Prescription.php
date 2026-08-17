<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    protected $fillable = [
        'prescription_code',
        'medical_record_id',
        'patient_id',
        'prescribed_by',
        'issued_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function prescribedBy(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'prescribed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function dispenses(): HasMany
    {
        return $this->hasMany(PrescriptionDispense::class);
    }
}
