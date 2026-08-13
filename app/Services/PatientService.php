<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PatientService
{
    public function paginate(
        array $filters
    ): LengthAwarePaginator {

        $perPage = min(
            max(
                (int) ($filters['per_page'] ?? 10),
                5
            ),
            50
        );

        $search = trim(
            (string) ($filters['search'] ?? '')
        );

        return Patient::query()

            ->with([
                'registeredBy:id,name,username',
            ])

            ->when(
                $search !== '',
                function ($query) use ($search) {

                    $query->where(
                        function ($query) use ($search) {

                            $query
                                ->where(
                                    'patient_code',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'first_name',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'last_name',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'phone',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'nic_passport',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )

            ->when(
                ($filters['status'] ?? null) === 'active',
                fn ($query) =>
                    $query->where('is_active', true)
            )

            ->when(
                ($filters['status'] ?? null) === 'inactive',
                fn ($query) =>
                    $query->where('is_active', false)
            )

            ->when(
                ! empty($filters['gender']),
                fn ($query) =>
                    $query->where(
                        'gender',
                        $filters['gender']
                    )
            )

            ->when(
                ! empty($filters['blood_group']),
                fn ($query) =>
                    $query->where(
                        'blood_group',
                        $filters['blood_group']
                    )
            )

            ->orderByDesc('id')

            ->paginate($perPage);
    }


    public function create(
        array $data,
        int $registeredBy
    ): Patient {

        return DB::transaction(
            function () use (
                $data,
                $registeredBy
            ) {

                $patient = Patient::create([
                    ...$data,

                    'patient_code' => null,

                    'registered_by' =>
                        $registeredBy,

                    'is_active' => true,
                ]);

                $patient->patient_code =
                    sprintf(
                        'PAT-%s-%06d',
                        now()->format('Y'),
                        $patient->id
                    );

                $patient->save();

                return $patient->fresh([
                    'registeredBy:id,name,username',
                ]);
            }
        );
    }


    public function update(
        Patient $patient,
        array $data
    ): Patient {

        $patient->fill($data);

        $patient->save();

        return $patient->fresh([
            'registeredBy:id,name,username',
        ]);
    }


    public function archive(
        Patient $patient
    ): void {

        DB::transaction(
            function () use ($patient) {

                $patient->is_active = false;

                $patient->save();

                $patient->delete();
            }
        );
    }


    public function storeDocument(
        Patient $patient,
        UploadedFile $file,
        array $data,
        int $uploadedBy
    ): PatientDocument {

        $path = $file->store(
            "patients/{$patient->id}/documents",
            'public'
        );

        try {

            return PatientDocument::create([
                'patient_id' => $patient->id,

                'uploaded_by' => $uploadedBy,

                'document_type' =>
                    $data['document_type'],

                'title' =>
                    $data['title'],

                'file_name' =>
                    $file->getClientOriginalName(),

                'file_path' =>
                    $path,

                'mime_type' =>
                    $file->getMimeType()
                    ?? 'application/octet-stream',

                'file_size' =>
                    $file->getSize(),

                'notes' =>
                    $data['notes'] ?? null,
            ]);

        } catch (Throwable $exception) {

            Storage::disk('public')
                ->delete($path);

            throw $exception;
        }
    }


    public function deleteDocument(
        PatientDocument $document
    ): void {

        Storage::disk('public')
            ->delete(
                $document->file_path
            );

        $document->delete();
    }
}