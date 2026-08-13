<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'phone',
        'email',
        'location',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(
            Doctor::class,
            'department_doctor'
        )
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function appointments(): HasMany
{
    return $this->hasMany(Appointment::class);
}
}