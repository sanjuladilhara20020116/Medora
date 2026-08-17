<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrescriptionDispense extends Model
{
    protected $fillable = [
        'dispense_code',
        'prescription_id',
        'patient_id',
        'dispensed_by',
        'status',
        'notes',
        'dispensed_at',
    ];

    protected function casts(): array
    {
        return ['dispensed_at' => 'datetime'];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function dispensedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionDispenseItem::class);
    }
}
