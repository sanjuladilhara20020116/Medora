<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'patient_code' => $this->patient_code,

            'first_name' => $this->first_name,
            'last_name' => $this->last_name,

            'full_name' => trim(
                $this->first_name . ' ' . $this->last_name
            ),

            'date_of_birth' =>
                $this->date_of_birth?->toDateString(),

            'age' =>
                $this->date_of_birth?->age,

            'gender' => $this->gender,

            'blood_group' => $this->blood_group,

            'nic_passport' => $this->nic_passport,

            'email' => $this->email,
            'phone' => $this->phone,
            'alternate_phone' => $this->alternate_phone,

            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'district' => $this->district,
            'postal_code' => $this->postal_code,
            'country' => $this->country,

            'emergency_contact_name' =>
                $this->emergency_contact_name,

            'emergency_contact_relation' =>
                $this->emergency_contact_relation,

            'emergency_contact_phone' =>
                $this->emergency_contact_phone,

            'allergies' => $this->allergies,

            'chronic_conditions' =>
                $this->chronic_conditions,

            'notes' => $this->notes,

            'is_active' => $this->is_active,

            'registered_by' => $this->whenLoaded(
                'registeredBy',
                fn () => [
                    'id' => $this->registeredBy?->id,
                    'name' => $this->registeredBy?->name,
                    'username' => $this->registeredBy?->username,
                ]
            ),

            'documents' => $this->whenLoaded(
                'documents',
                fn () => $this->documents->map(
                    fn ($document) => [
                        'id' => $document->id,

                        'document_type' =>
                            $document->document_type,

                        'title' =>
                            $document->title,

                        'file_name' =>
                            $document->file_name,

                        'file_url' =>
                            Storage::disk('public')
                                ->url($document->file_path),

                        'mime_type' =>
                            $document->mime_type,

                        'file_size' =>
                            $document->file_size,

                        'notes' =>
                            $document->notes,

                        'created_at' =>
                            $document->created_at
                                ?->toIso8601String(),
                    ]
                )
            ),

            'created_at' =>
                $this->created_at?->toIso8601String(),

            'updated_at' =>
                $this->updated_at?->toIso8601String(),
        ];
    }
}