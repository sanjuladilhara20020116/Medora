<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\LabRequest;
use App\Models\LabResult;
use App\Models\LabTest;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LaboratoryService
{
    public function paginateTests(array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 5), 50);
        $search = trim((string) ($filters['search'] ?? ''));

        return LabTest::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('test_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when(! empty($filters['category']), fn ($query) => $query->where('category', $filters['category']))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function createTest(array $data): LabTest
    {
        return DB::transaction(function () use ($data) {
            $test = LabTest::create([
                ...$data,
                'test_code' => null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $test->update([
                'test_code' => sprintf('LAB-%s-%04d', now()->format('Y'), $test->id),
            ]);

            return $test->fresh();
        });
    }

    public function updateTest(LabTest $test, array $data): LabTest
    {
        $test->update($data);

        return $test->fresh();
    }

    public function archiveTest(LabTest $test): void
    {
        $test->update(['is_active' => false]);
    }

    public function paginateRequests(array $filters, User $user): LengthAwarePaginator
    {
        $user->loadMissing('role', 'doctor');

        $perPage = min(max((int) ($filters['per_page'] ?? 10), 5), 50);
        $search = trim((string) ($filters['search'] ?? ''));
        $query = LabRequest::query()->with($this->requestRelations());

        if ($user->role?->slug === 'DOCTOR') {
            $query->where('doctor_id', $this->doctorIdFor($user));
        }

        $query
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('request_code', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($query) use ($search) {
                            $query->where('patient_code', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('labTest', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['patient_id']), fn ($query) => $query->where('patient_id', $filters['patient_id']))
            ->when(! empty($filters['lab_test_id']), fn ($query) => $query->where('lab_test_id', $filters['lab_test_id']))
            ->when(! empty($filters['requested_on']), fn ($query) => $query->whereDate('requested_at', $filters['requested_on']));

        return $query->orderByDesc('requested_at')->paginate($perPage);
    }

    public function createRequest(array $data, User $user): LabRequest
    {
        $this->ensureActivePatient((int) $data['patient_id']);
        $this->ensureActiveTest((int) $data['lab_test_id']);
        $doctorId = $this->resolveDoctorId($data, $user);
        $this->validateMedicalRecord($data['medical_record_id'] ?? null, (int) $data['patient_id'], $doctorId);

        return DB::transaction(function () use ($data, $doctorId, $user) {
            $request = LabRequest::create([
                ...Arr::only($data, [
                    'patient_id',
                    'lab_test_id',
                    'medical_record_id',
                    'priority',
                    'clinical_notes',
                ]),
                'request_code' => null,
                'doctor_id' => $doctorId,
                'requested_by' => $user->id,
                'status' => 'REQUESTED',
                'requested_at' => now(),
            ]);

            $request->update([
                'request_code' => sprintf('LBR-%s-%06d', now()->format('Y'), $request->id),
            ]);

            return $request->fresh($this->requestRelations());
        });
    }

    public function collectSample(LabRequest $request, array $data, User $user): LabRequest
    {
        $this->ensureStatus($request, ['REQUESTED'], 'A sample can only be collected for a requested test.');

        $request->update([
            ...$data,
            'status' => 'SAMPLE_COLLECTED',
            'sample_collected_at' => now(),
            'sample_collected_by' => $user->id,
        ]);

        return $request->fresh($this->requestRelations());
    }

    public function startProcessing(LabRequest $request): LabRequest
    {
        $this->ensureStatus($request, ['SAMPLE_COLLECTED'], 'Only a collected sample can be moved to processing.');

        $request->update([
            'status' => 'PROCESSING',
            'processing_started_at' => now(),
        ]);

        return $request->fresh($this->requestRelations());
    }

    public function saveResult(LabRequest $request, array $data, User $user): LabRequest
    {
        $this->ensureStatus(
            $request,
            ['SAMPLE_COLLECTED', 'PROCESSING', 'COMPLETED'],
            'A result can only be entered after the sample has been collected.'
        );

        DB::transaction(function () use ($request, $data, $user) {
            LabResult::updateOrCreate(
                ['lab_request_id' => $request->id],
                [
                    ...$data,
                    'entered_by' => $user->id,
                ]
            );

            $request->update([
                'status' => 'COMPLETED',
                'processing_started_at' => $request->processing_started_at ?? now(),
                'completed_at' => now(),
            ]);
        });

        return $request->fresh($this->requestRelations());
    }

    public function loadRequest(LabRequest $request): LabRequest
    {
        return $request->load($this->requestRelations());
    }

    public function ensureCanView(LabRequest $request, User $user): void
    {
        $user->loadMissing('role', 'doctor');

        if ($user->role?->slug === 'DOCTOR' && $request->doctor_id !== $this->doctorIdFor($user)) {
            abort(403, 'You can only access laboratory requests assigned to you.');
        }
    }

    private function requestRelations(): array
    {
        return [
            'patient:id,patient_code,first_name,last_name,date_of_birth,gender,blood_group,phone',
            'doctor:id,user_id,doctor_code,specialization',
            'doctor.user:id,name',
            'labTest:id,test_code,name,category,specimen_type,unit,reference_range,turnaround_hours',
            'medicalRecord:id,record_code,diagnosis',
            'requestedBy:id,name',
            'sampleCollectedBy:id,name',
            'result.enteredBy:id,name',
            'result.verifiedBy:id,name',
        ];
    }

    private function resolveDoctorId(array $data, User $user): int
    {
        $user->loadMissing('role', 'doctor');

        if ($user->role?->slug === 'DOCTOR') {
            $doctorId = $this->doctorIdFor($user);

            if (isset($data['doctor_id']) && (int) $data['doctor_id'] !== $doctorId) {
                throw ValidationException::withMessages([
                    'doctor_id' => ['Doctors can only create laboratory requests under their own profile.'],
                ]);
            }

            return $doctorId;
        }

        if (empty($data['doctor_id'])) {
            throw ValidationException::withMessages([
                'doctor_id' => ['A doctor must be selected for this laboratory request.'],
            ]);
        }

        $doctorId = Doctor::query()
            ->whereKey($data['doctor_id'])
            ->where('is_active', true)
            ->value('id');

        if (! $doctorId) {
            throw ValidationException::withMessages([
                'doctor_id' => ['The selected doctor is not active.'],
            ]);
        }

        return (int) $doctorId;
    }

    private function doctorIdFor(User $user): int
    {
        $doctorId = $user->doctor?->id;

        if (! $doctorId) {
            abort(403, 'No active doctor profile is associated with this account.');
        }

        return $doctorId;
    }

    private function ensureActivePatient(int $patientId): void
    {
        if (! Patient::query()->whereKey($patientId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'patient_id' => ['The selected patient is not active.'],
            ]);
        }
    }

    private function ensureActiveTest(int $testId): void
    {
        if (! LabTest::query()->whereKey($testId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'lab_test_id' => ['The selected laboratory test is not active.'],
            ]);
        }
    }

    private function validateMedicalRecord(?int $recordId, int $patientId, int $doctorId): void
    {
        if (! $recordId) {
            return;
        }

        if (! MedicalRecord::query()
            ->whereKey($recordId)
            ->where('patient_id', $patientId)
            ->where('doctor_id', $doctorId)
            ->exists()) {
            throw ValidationException::withMessages([
                'medical_record_id' => ['Choose a medical record for the selected patient and doctor.'],
            ]);
        }
    }

    private function ensureStatus(LabRequest $request, array $allowedStatuses, string $message): void
    {
        if (! in_array($request->status, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => [$message],
            ]);
        }
    }
}
