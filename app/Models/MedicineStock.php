<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineStock extends Model
{
    protected $fillable = [
        'medicine_id',
        'batch_number',
        'expiry_date',
        'received_date',
        'quantity_received',
        'quantity_available',
        'unit_cost',
        'selling_price',
        'supplier',
        'received_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'received_date' => 'date',
            'quantity_received' => 'decimal:2',
            'quantity_available' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function dispenseItems(): HasMany
    {
        return $this->hasMany(PrescriptionDispenseItem::class);
    }
}
