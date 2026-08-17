<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabTest extends Model
{
    protected $fillable = [
        'test_code',
        'name',
        'category',
        'specimen_type',
        'unit',
        'reference_range',
        'turnaround_hours',
        'price',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'turnaround_hours' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function requests(): HasMany
    {
        return $this->hasMany(LabRequest::class);
    }
}
