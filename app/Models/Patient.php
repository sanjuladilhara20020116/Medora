<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_code',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'blood_group',
        'nic_passport',
        'email',
        'phone',
        'alternate_phone',
        'address_line_1',
        'address_line_2',
        'city',
        'district',
        'postal_code',
        'country',
        'emergency_contact_name',
        'emergency_contact_relation',
        'emergency_contact_phone',
        'allergies',
        'chronic_conditions',
        'notes',
        'registered_by',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'registered_by'
        );
    }

    public function documents(): HasMany
    {
        return $this->hasMany(
            PatientDocument::class
        );
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }
}
