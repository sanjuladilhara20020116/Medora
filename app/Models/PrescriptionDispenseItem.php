<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionDispenseItem extends Model
{
    protected $fillable = [
        'prescription_dispense_id',
        'prescription_item_id',
        'medicine_id',
        'medicine_stock_id',
        'quantity_dispensed',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity_dispensed' => 'decimal:2',
            'unit_price' => 'decimal:2',
        ];
    }

    public function dispense(): BelongsTo
    {
        return $this->belongsTo(PrescriptionDispense::class, 'prescription_dispense_id');
    }

    public function prescriptionItem(): BelongsTo
    {
        return $this->belongsTo(PrescriptionItem::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(MedicineStock::class, 'medicine_stock_id');
    }
}
