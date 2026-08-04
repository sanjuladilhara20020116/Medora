<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    /**
     * Fields that may be mass assigned.
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'phone',
        'email',
        'location',
        'is_active',
    ];

    /**
     * Attribute type casting.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}