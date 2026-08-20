<?php

namespace App\Services;

use App\Mail\PatientAccountCreated;
use App\Models\Patient;
use App\Models\PatientDocument;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

                $patientRole = Role::query()
                    ->where('slug', 'PATIENT')
                    ->where('is_active', true)
                    ->firstOrFail();

                $defaultPassword = Str::password(
                    14,
                    true,
                    true,
                    false,
                    false
                );

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

                $user = User::create([
                    'name' => trim(
                        $patient->first_name.' '.$patient->last_name
                    ),
                    'username' => sprintf(
                        'patient-%s-%06d',
                        now()->format('Y'),
                        $patient->id
                    ),
                    'email' => $patient->email,
                    'phone' => $patient->phone,
                    'password' => $defaultPassword,
                    'role_id' => $patientRole->id,
                    'is_active' => true,
                ]);

                $patient->user_id = $user->id;

                $patient->save();

                Mail::to($user->email)->send(
                    new PatientAccountCreated(
                        $patient,
                        $user,
                        $defaultPassword
                    )
                );

                return $patient->fresh([
                    'registeredBy:id,name,username',
                    'user:id,name,username,email,phone',
                ]);
            }
        );
    }


    public function update(
        Patient $patient,
        array $data
    ): Patient {

        return DB::transaction(function () use ($patient, $data) {
            $patient->fill($data);
            $patient->save();

            if ($patient->user) {
                $patient->user->update([
                    'name' => trim(
                        $patient->first_name.' '.$patient->last_name
                    ),
                    'email' => $patient->email,
                    'phone' => $patient->phone,
                ]);
            }

            return $patient->fresh([
                'registeredBy:id,name,username',
                'user:id,name,username,email,phone',
            ]);
        });
    }


    public function archive(
        Patient $patient
    ): void {

        DB::transaction(
            function () use ($patient) {

                $user = $patient->user;

                $patient->is_active = false;

                $patient->save();

                $patient->delete();

                if ($user) {
                    $user->is_active = false;
                    $user->save();
                    $user->delete();
                }
            }
        );
    }

    public function updatePortalProfile(
        Patient $patient,
        array $data
    ): Patient {
        return DB::transaction(function () use ($patient, $data) {
            $patient->update(Arr::only($data, [
                'first_name',
                'last_name',
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
            ]));

            $userData = [
                'name' => trim(
                    $patient->first_name.' '.$patient->last_name
                ),
                'email' => $patient->email,
                'phone' => $patient->phone,
            ];

            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $patient->user->update($userData);

            return $patient->fresh([
                'user:id,name,username,email,phone,last_login_at',
            ]);
        });
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
