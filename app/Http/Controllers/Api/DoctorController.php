<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctors\StoreDoctorRequest;
use App\Http\Requests\Doctors\StoreDoctorScheduleRequest;
use App\Http\Requests\Doctors\UpdateDoctorProfileRequest;
use App\Http\Requests\Doctors\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\MedicalRecord;
use App\Models\User;
use App\Services\DoctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DoctorController extends Controller
{
    public function __construct(
        private DoctorService $doctorService
    ) {}

    public function index(Request $request)
    {
        $filters =
            $request->validate([
                'search' => 'nullable|string|max:255',

                'department_id' => 'nullable|integer|exists:departments,id',

                'status' => 'nullable|in:active,inactive',

                'per_page' => 'nullable|integer|min:5|max:50',
            ]);

        return DoctorResource::collection(
            $this
                ->doctorService
                ->paginate($filters)
        )->additional([
            'success' => true,

            'message' => 'Doctors retrieved successfully.',
        ]);
    }

    public function store(
        StoreDoctorRequest $request
    ): JsonResponse {

        $doctor =
            $this
                ->doctorService
                ->create(
                    $request->validated()
                );

        return response()->json([
            'success' => true,

            'message' => 'Doctor created successfully. A default password has been sent to the doctor email address.',

            'data' => (new DoctorResource(
                $doctor
            ))->resolve(),
        ], 201);
    }

    public function show(
        Doctor $doctor
    ): JsonResponse {

        $doctor->load([
            'user:id,name,username,email,phone',

            'departments:id,code,name',

            'schedules.department:id,name',
        ]);

        return response()->json([
            'success' => true,

            'data' => (new DoctorResource(
                $doctor
            ))->resolve(),
        ]);
    }

    public function update(
        UpdateDoctorRequest $request,
        Doctor $doctor
    ): JsonResponse {

        $doctor =
            $this
                ->doctorService
                ->update(
                    $doctor,
                    $request->validated()
                );

        return response()->json([
            'success' => true,

            'message' => 'Doctor updated successfully.',

            'data' => (new DoctorResource(
                $doctor
            ))->resolve(),
        ]);
    }

    public function destroy(
        Doctor $doctor
    ): JsonResponse {

        $this
            ->doctorService
            ->archive($doctor);

        return response()->json([
            'success' => true,

            'message' => 'Doctor archived successfully.',
        ]);
    }

    public function storeSchedule(
        StoreDoctorScheduleRequest $request,
        Doctor $doctor
    ): JsonResponse {

        $schedule =
            $this
                ->doctorService
                ->createSchedule(
                    $doctor,
                    $request->validated()
                );

        return response()->json([
            'success' => true,

            'message' => 'Doctor schedule added successfully.',

            'data' => [
                'id' => $schedule->id,
            ],
        ], 201);
    }

    public function destroySchedule(
        Doctor $doctor,
        DoctorSchedule $schedule
    ): JsonResponse {

        $this
            ->doctorService
            ->deleteSchedule(
                $doctor,
                $schedule
            );

        return response()->json([
            'success' => true,

            'message' => 'Doctor schedule removed successfully.',
        ]);
    }

    public function dashboard(): JsonResponse
    {
        $doctor = $this->activeDoctorFor(
            Auth::guard('api')->user()
        );

        $todayAppointments = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', today())
            ->where('status', '!=', 'CANCELLED');

        $upcomingAppointments = Appointment::query()
            ->with([
                'patient:id,patient_code,first_name,last_name',
                'department:id,name',
            ])
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', '>=', today())
            ->where('status', '!=', 'CANCELLED')
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->limit(6)
            ->get()
            ->map(fn (Appointment $appointment) => [
                'id' => $appointment->id,
                'appointment_code' => $appointment->appointment_code,
                'appointment_date' => $appointment->appointment_date?->toDateString(),
                'start_time' => substr((string) $appointment->start_time, 0, 5),
                'status' => $appointment->status,
                'patient' => [
                    'id' => $appointment->patient?->id,
                    'patient_code' => $appointment->patient?->patient_code,
                    'full_name' => trim(($appointment->patient?->first_name ?? '').' '.($appointment->patient?->last_name ?? '')),
                ],
                'department' => $appointment->department?->name,
            ]);

        $recentRecords = MedicalRecord::query()
            ->with('patient:id,patient_code,first_name,last_name')
            ->where('doctor_id', $doctor->id)
            ->orderByDesc('recorded_at')
            ->limit(5)
            ->get()
            ->map(fn (MedicalRecord $record) => [
                'id' => $record->id,
                'record_code' => $record->record_code,
                'recorded_at' => $record->recorded_at?->toIso8601String(),
                'diagnosis' => $record->diagnosis,
                'patient' => [
                    'id' => $record->patient?->id,
                    'full_name' => trim(($record->patient?->first_name ?? '').' '.($record->patient?->last_name ?? '')),
                ],
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'doctor' => (new DoctorResource($doctor))->resolve(),
                'statistics' => [
                    'appointments_today' => $todayAppointments->count(),
                    'pending_today' => (clone $todayAppointments)->whereIn('status', ['SCHEDULED', 'CHECKED_IN', 'IN_PROGRESS'])->count(),
                    'medical_records' => MedicalRecord::query()->where('doctor_id', $doctor->id)->count(),
                    'patients_treated' => MedicalRecord::query()->where('doctor_id', $doctor->id)->distinct()->count('patient_id'),
                ],
                'upcoming_appointments' => $upcomingAppointments,
                'recent_medical_records' => $recentRecords,
            ],
        ]);
    }

    public function profile(): JsonResponse
    {
        $doctor = $this->activeDoctorFor(
            Auth::guard('api')->user()
        );

        return response()->json([
            'success' => true,
            'data' => (new DoctorResource($doctor))->resolve(),
        ]);
    }

    public function updateProfile(
        UpdateDoctorProfileRequest $request
    ): JsonResponse {
        $user = Auth::guard('api')->user();
        $data = $request->validated();

        if (
            ! empty($data['password'])
            && ! Hash::check($data['current_password'], $user->password)
        ) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $userData = Arr::only($data, [
            'name',
            'username',
            'email',
            'phone',
        ]);

        if (! empty($data['password'])) {
            $userData['password'] = $data['password'];
        }

        $user->update($userData);

        $doctor = $this->activeDoctorFor($user);

        return response()->json([
            'success' => true,
            'message' => 'Profile details updated successfully.',
            'data' => (new DoctorResource($doctor))->resolve(),
        ]);
    }

    private function activeDoctorFor(User $user): Doctor
    {
        $doctor = Doctor::query()
            ->with([
                'user:id,name,username,email,phone',
                'departments:id,code,name',
                'schedules.department:id,name',
            ])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $doctor) {
            abort(403, 'No active doctor profile is associated with this account.');
        }

        return $doctor;
    }
}
