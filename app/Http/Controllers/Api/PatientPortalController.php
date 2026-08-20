<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patients\UpdatePatientPortalProfileRequest;
use App\Models\Patient;
use App\Models\User;
use App\Services\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PatientPortalController extends Controller
{
    public function __construct(
        private PatientService $patientService
    ) {}

    public function dashboard(): JsonResponse
    {
        $patient = $this->activePatientFor(
            Auth::guard('api')->user()
        );

        return response()->json([
            'success' => true,
            'data' => [
                'patient' => $this->portalPayload($patient),
            ],
        ]);
    }

    public function profile(): JsonResponse
    {
        $patient = $this->activePatientFor(
            Auth::guard('api')->user()
        );

        return response()->json([
            'success' => true,
            'data' => [
                'patient' => $this->portalPayload($patient),
            ],
        ]);
    }

    public function updateProfile(
        UpdatePatientPortalProfileRequest $request
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

        $patient = $this->patientService->updatePortalProfile(
            $this->activePatientFor($user),
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Your profile details have been updated successfully.',
            'data' => [
                'patient' => $this->portalPayload($patient),
            ],
        ]);
    }

    private function activePatientFor(User $user): Patient
    {
        $patient = Patient::query()
            ->with('user:id,name,username,email,phone,last_login_at')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $patient) {
            abort(403, 'No active patient profile is associated with this account.');
        }

        return $patient;
    }

    private function portalPayload(Patient $patient): array
    {
        return [
            'id' => $patient->id,
            'patient_code' => $patient->patient_code,
            'first_name' => $patient->first_name,
            'last_name' => $patient->last_name,
            'full_name' => trim($patient->first_name.' '.$patient->last_name),
            'date_of_birth' => $patient->date_of_birth?->toDateString(),
            'age' => $patient->date_of_birth?->age,
            'gender' => $patient->gender,
            'blood_group' => $patient->blood_group,
            'nic_passport' => $patient->nic_passport,
            'email' => $patient->email,
            'phone' => $patient->phone,
            'alternate_phone' => $patient->alternate_phone,
            'address_line_1' => $patient->address_line_1,
            'address_line_2' => $patient->address_line_2,
            'city' => $patient->city,
            'district' => $patient->district,
            'postal_code' => $patient->postal_code,
            'country' => $patient->country,
            'emergency_contact_name' => $patient->emergency_contact_name,
            'emergency_contact_relation' => $patient->emergency_contact_relation,
            'emergency_contact_phone' => $patient->emergency_contact_phone,
            'account' => [
                'username' => $patient->user?->username,
                'email' => $patient->user?->email,
                'last_login_at' => $patient->user?->last_login_at?->toIso8601String(),
            ],
        ];
    }
}
