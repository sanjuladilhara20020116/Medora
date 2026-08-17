<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LabRequest extends Model
{
    protected $fillable = [
        'request_code',
        'patient_id',
        'doctor_id',
        'lab_test_id',
        'medical_record_id',
        'requested_by',
        'priority',
        'status',
        'clinical_notes',
        'requested_at',
        'sample_collected_at',
        'sample_collected_by',
        'sample_identifier',
        'specimen_condition',
        'sample_notes',
        'processing_started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'sample_collected_at' => 'datetime',
            'processing_started_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTest::class);
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function sampleCollectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sample_collected_by');
    }

    public function result(): HasOne
    {
        return $this->hasOne(LabResult::class);
    }
}
