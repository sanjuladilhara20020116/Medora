<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patients\StorePatientDocumentRequest;
use App\Http\Requests\Patients\StorePatientRequest;
use App\Http\Requests\Patients\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Models\PatientDocument;
use App\Services\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    public function __construct(
        private PatientService $patientService
    ) {
    }


    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                'in:active,inactive',
            ],

            'gender' => [
                'nullable',
                'in:MALE,FEMALE,OTHER,PREFER_NOT_TO_SAY',
            ],

            'blood_group' => [
                'nullable',
                'in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:5',
                'max:50',
            ],
        ]);

        $patients =
            $this->patientService
                ->paginate($filters);

        return PatientResource::collection(
            $patients
        )->additional([
            'success' => true,

            'message' =>
                'Patients retrieved successfully.',
        ]);
    }


    public function store(
        StorePatientRequest $request
    ): JsonResponse {

        $patient =
            $this->patientService->create(
                $request->validated(),
                Auth::guard('api')->id()
            );

        return response()->json([
            'success' => true,

            'message' =>
                'Patient registered successfully.',

            'data' =>
                (new PatientResource($patient))
                    ->resolve(),
        ], 201);
    }


    public function show(
        Patient $patient
    ): JsonResponse {

        $patient->load([
            'registeredBy:id,name,username',

            'documents' => fn ($query) =>
                $query->latest(),
        ]);

        return response()->json([
            'success' => true,

            'message' =>
                'Patient retrieved successfully.',

            'data' =>
                (new PatientResource($patient))
                    ->resolve(),
        ]);
    }


    public function update(
        UpdatePatientRequest $request,
        Patient $patient
    ): JsonResponse {

        $patient =
            $this->patientService->update(
                $patient,
                $request->validated()
            );

        return response()->json([
            'success' => true,

            'message' =>
                'Patient updated successfully.',

            'data' =>
                (new PatientResource($patient))
                    ->resolve(),
        ]);
    }


    public function destroy(
        Patient $patient
    ): JsonResponse {

        $this->patientService
            ->archive($patient);

        return response()->json([
            'success' => true,

            'message' =>
                'Patient archived successfully.',
        ]);
    }


    public function storeDocument(
        StorePatientDocumentRequest $request,
        Patient $patient
    ): JsonResponse {

        $data =
            $request->validated();

        $document =
            $this->patientService
                ->storeDocument(
                    $patient,

                    $request->file('file'),

                    $data,

                    Auth::guard('api')->id()
                );

        return response()->json([
            'success' => true,

            'message' =>
                'Patient document uploaded successfully.',

            'data' => [
                'id' => $document->id,
            ],
        ], 201);
    }


    public function destroyDocument(
        Patient $patient,
        PatientDocument $document
    ): JsonResponse {

        if (
            $document->patient_id
            !== $patient->id
        ) {
            abort(404);
        }

        $this->patientService
            ->deleteDocument($document);

        return response()->json([
            'success' => true,

            'message' =>
                'Patient document deleted successfully.',
        ]);
    }
}