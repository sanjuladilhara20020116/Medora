<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalRecords\StoreMedicalRecordRequest;
use App\Http\Requests\MedicalRecords\StoreMedicalReportRequest;
use App\Http\Requests\MedicalRecords\StorePrescriptionRequest;
use App\Http\Requests\MedicalRecords\UpdateMedicalRecordRequest;
use App\Http\Resources\MedicalRecordResource;
use App\Models\MedicalRecord;
use App\Models\MedicalReport;
use App\Services\MedicalRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MedicalRecordController extends Controller
{
    public function __construct(private MedicalRecordService $medicalRecordService) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
            'recorded_on' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        return MedicalRecordResource::collection(
            $this->medicalRecordService->paginate($filters, Auth::guard('api')->user())
        )->additional([
            'success' => true,
            'message' => 'Medical records retrieved successfully.',
        ]);
    }

    public function store(StoreMedicalRecordRequest $request): JsonResponse
    {
        $record = $this->medicalRecordService->create($request->validated(), Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Medical record created successfully.',
            'data' => (new MedicalRecordResource($record))->resolve(),
        ], 201);
    }

    public function show(MedicalRecord $medicalRecord): JsonResponse
    {
        $this->medicalRecordService->ensureCanView($medicalRecord, Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Medical record retrieved successfully.',
            'data' => (new MedicalRecordResource($medicalRecord->load([
                'patient:id,patient_code,first_name,last_name,date_of_birth,gender,blood_group,allergies,chronic_conditions,phone',
                'doctor:id,user_id,doctor_code,specialization',
                'doctor.user:id,name',
                'appointment:id,appointment_code,appointment_date,start_time,status',
                'prescription.items',
                'reports' => fn ($query) => $query->latest(),
            ])))->resolve(),
        ]);
    }

    public function update(UpdateMedicalRecordRequest $request, MedicalRecord $medicalRecord): JsonResponse
    {
        $record = $this->medicalRecordService->update($medicalRecord, $request->validated(), Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Medical record updated successfully.',
            'data' => (new MedicalRecordResource($record))->resolve(),
        ]);
    }

    public function savePrescription(StorePrescriptionRequest $request, MedicalRecord $medicalRecord): JsonResponse
    {
        $record = $this->medicalRecordService->savePrescription($medicalRecord, $request->validated(), Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Prescription saved successfully.',
            'data' => (new MedicalRecordResource($record))->resolve(),
        ]);
    }

    public function storeReport(StoreMedicalReportRequest $request, MedicalRecord $medicalRecord): JsonResponse
    {
        $report = $this->medicalRecordService->storeReport($medicalRecord, $request->file('file'), $request->validated(), Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Medical report uploaded successfully.',
            'data' => ['id' => $report->id],
        ], 201);
    }

    public function downloadReport(MedicalRecord $medicalRecord, MedicalReport $report): BinaryFileResponse
    {
        return $this->medicalRecordService->downloadReport($medicalRecord, $report, Auth::guard('api')->user());
    }

    public function destroyReport(MedicalRecord $medicalRecord, MedicalReport $report): JsonResponse
    {
        $this->medicalRecordService->deleteReport($medicalRecord, $report, Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Medical report deleted successfully.',
        ]);
    }
}
