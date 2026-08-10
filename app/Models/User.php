<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Fields that may be mass assigned.
     */
    protected $fillable = [
        'role_id',
        'name',
        'username',
        'email',
        'phone',
        'password',
        'is_active',
        'last_login_at',
    ];

    /**
     * Fields hidden from array / JSON responses.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute type casting.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * A user belongs to one role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Return the identifier that will be stored
     * in the JWT subject claim.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return additional custom claims for JWT.
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}