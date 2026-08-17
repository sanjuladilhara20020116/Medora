<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    protected $fillable = [
        'medicine_code',
        'medicine_category_id',
        'name',
        'generic_name',
        'manufacturer',
        'dosage_form',
        'strength',
        'reorder_level',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'reorder_level' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MedicineCategory::class, 'medicine_category_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(MedicineStock::class);
    }

    public function dispenseItems(): HasMany
    {
        return $this->hasMany(PrescriptionDispenseItem::class);
    }
}
