<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'uploaded_by',
        'document_type',
        'title',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'notes',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }
}