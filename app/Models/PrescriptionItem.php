<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrescriptionItem extends Model
{
    protected $fillable = [
        'prescription_id',
        'medicine_name',
        'dosage',
        'frequency',
        'duration_days',
        'quantity',
        'instructions',
    ];

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'quantity' => 'decimal:2',
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function dispenseItems(): HasMany
    {
        return $this->hasMany(PrescriptionDispenseItem::class);
    }
}
