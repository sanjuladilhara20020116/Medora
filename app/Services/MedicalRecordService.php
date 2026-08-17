<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\MedicalReport;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class MedicalRecordService
{
    public function paginate(array $filters, User $user): LengthAwarePaginator
    {
        $user->loadMissing('role', 'doctor');

        $perPage = min(max((int) ($filters['per_page'] ?? 10), 5), 50);
        $search = trim((string) ($filters['search'] ?? ''));

        $query = MedicalRecord::query()->with($this->relations());

        if ($user->role?->slug === 'DOCTOR') {
            $query->where('doctor_id', $this->doctorIdFor($user));
        }

        $query
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('record_code', 'like', "%{$search}%")
                        ->orWhere('diagnosis', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($query) use ($search) {
                            $query->where('patient_code', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(! empty($filters['patient_id']), fn ($query) => $query->where('patient_id', $filters['patient_id']))
            ->when(! empty($filters['doctor_id']) && $user->role?->slug !== 'DOCTOR', fn ($query) => $query->where('doctor_id', $filters['doctor_id']))
            ->when(! empty($filters['recorded_on']), fn ($query) => $query->whereDate('recorded_at', $filters['recorded_on']));

        return $query->orderByDesc('recorded_at')->paginate($perPage);
    }

    public function create(array $data, User $user): MedicalRecord
    {
        $this->ensureActivePatient((int) $data['patient_id']);

        $doctorId = $this->resolveDoctorId($data, $user);
        $this->validateAppointment($data['appointment_id'] ?? null, (int) $data['patient_id'], $doctorId);

        return DB::transaction(function () use ($data, $doctorId, $user) {
            $record = MedicalRecord::create([
                ...Arr::only($data, [
                    'patient_id',
                    'appointment_id',
                    'recorded_at',
                    'chief_complaint',
                    'diagnosis',
                    'treatment_plan',
                    'clinical_notes',
                    'follow_up_date',
                    'follow_up_notes',
                ]),
                'record_code' => null,
                'doctor_id' => $doctorId,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $record->update([
                'record_code' => sprintf('EMR-%s-%06d', now()->format('Y'), $record->id),
            ]);

            return $record->fresh($this->relations());
        });
    }

    public function update(MedicalRecord $record, array $data, User $user): MedicalRecord
    {
        $this->ensureCanManage($record, $user);

        $record->update([
            ...Arr::only($data, [
                'recorded_at',
                'chief_complaint',
                'diagnosis',
                'treatment_plan',
                'clinical_notes',
                'follow_up_date',
                'follow_up_notes',
            ]),
            'updated_by' => $user->id,
        ]);

        return $record->fresh($this->relations());
    }

    public function savePrescription(MedicalRecord $record, array $data, User $user): MedicalRecord
    {
        $this->ensureCanManage($record, $user);

        DB::transaction(function () use ($record, $data) {
            $prescription = $record->prescription;

            if (! $prescription) {
                $prescription = Prescription::create([
                    'prescription_code' => null,
                    'medical_record_id' => $record->id,
                    'patient_id' => $record->patient_id,
                    'prescribed_by' => $record->doctor_id,
                    'issued_at' => $data['issued_at'],
                    'notes' => $data['notes'] ?? null,
                ]);

                $prescription->update([
                    'prescription_code' => sprintf('RX-%s-%06d', now()->format('Y'), $prescription->id),
                ]);
            } else {
                $prescription->update([
                    'issued_at' => $data['issued_at'],
                    'notes' => $data['notes'] ?? null,
                ]);
            }

            $prescription->items()->delete();
            $prescription->items()->createMany($data['items']);
        });

        return $record->fresh($this->relations());
    }

    public function storeReport(MedicalRecord $record, UploadedFile $file, array $data, User $user): MedicalReport
    {
        $this->ensureCanManage($record, $user);

        $path = $file->store("medical-records/{$record->id}/reports", 'local');

        try {
            return MedicalReport::create([
                'medical_record_id' => $record->id,
                'uploaded_by' => $user->id,
                'report_type' => $data['report_type'],
                'title' => $data['title'],
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'file_size' => $file->getSize(),
                'notes' => $data['notes'] ?? null,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }
    }

    public function deleteReport(MedicalRecord $record, MedicalReport $report, User $user): void
    {
        $this->ensureCanManage($record, $user);
        $this->ensureReportBelongsToRecord($record, $report);

        Storage::disk('local')->delete($report->file_path);
        $report->delete();
    }

    public function downloadReport(MedicalRecord $record, MedicalReport $report, User $user): BinaryFileResponse
    {
        $this->ensureCanView($record, $user);
        $this->ensureReportBelongsToRecord($record, $report);

        if (! Storage::disk('local')->exists($report->file_path)) {
            abort(404, 'The medical report file is no longer available.');
        }

        return Storage::disk('local')->download($report->file_path, $report->file_name);
    }

    public function ensureCanView(MedicalRecord $record, User $user): void
    {
        $user->loadMissing('role', 'doctor');

        if ($user->role?->slug === 'DOCTOR' && $record->doctor_id !== $this->doctorIdFor($user)) {
            abort(403, 'You can only access your own medical records.');
        }
    }

    public function ensureCanManage(MedicalRecord $record, User $user): void
    {
        $this->ensureCanView($record, $user);
    }

    private function relations(): array
    {
        return [
            'patient:id,patient_code,first_name,last_name,date_of_birth,gender,blood_group,allergies,chronic_conditions,phone',
            'doctor:id,user_id,doctor_code,specialization',
            'doctor.user:id,name',
            'appointment:id,appointment_code,appointment_date,start_time,status',
            'prescription.items',
            'reports' => fn ($query) => $query->latest(),
        ];
    }

    private function resolveDoctorId(array $data, User $user): int
    {
        $user->loadMissing('role', 'doctor');

        if ($user->role?->slug === 'DOCTOR') {
            $doctorId = $this->doctorIdFor($user);

            if (isset($data['doctor_id']) && (int) $data['doctor_id'] !== $doctorId) {
                throw ValidationException::withMessages([
                    'doctor_id' => ['Doctors can only create records under their own profile.'],
                ]);
            }

            return $doctorId;
        }

        if (empty($data['doctor_id'])) {
            throw ValidationException::withMessages([
                'doctor_id' => ['A doctor must be selected for this medical record.'],
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

    private function validateAppointment(?int $appointmentId, int $patientId, int $doctorId): void
    {
        if (! $appointmentId) {
            return;
        }

        $appointment = Appointment::query()
            ->whereKey($appointmentId)
            ->where('patient_id', $patientId)
            ->where('doctor_id', $doctorId)
            ->whereIn('status', ['IN_PROGRESS', 'COMPLETED'])
            ->first();

        if (! $appointment) {
            throw ValidationException::withMessages([
                'appointment_id' => ['Choose an in-progress or completed appointment for the selected patient and doctor.'],
            ]);
        }

        if ($appointment->medicalRecord()->exists()) {
            throw ValidationException::withMessages([
                'appointment_id' => ['This appointment already has a medical record.'],
            ]);
        }
    }

    private function ensureActivePatient(int $patientId): void
    {
        if (! Patient::query()->whereKey($patientId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'patient_id' => ['The selected patient is not active.'],
            ]);
        }
    }

    private function ensureReportBelongsToRecord(MedicalRecord $record, MedicalReport $report): void
    {
        if ($report->medical_record_id !== $record->id) {
            abort(404);
        }
    }
}
