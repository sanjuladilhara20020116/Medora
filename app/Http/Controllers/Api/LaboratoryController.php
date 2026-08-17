<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Laboratory\CollectSampleRequest;
use App\Http\Requests\Laboratory\StoreLabRequestRequest;
use App\Http\Requests\Laboratory\StoreLabResultRequest;
use App\Http\Requests\Laboratory\StoreLabTestRequest;
use App\Http\Requests\Laboratory\UpdateLabTestRequest;
use App\Http\Resources\LabRequestResource;
use App\Http\Resources\LabTestResource;
use App\Models\LabRequest;
use App\Models\LabTest;
use App\Services\LaboratoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaboratoryController extends Controller
{
    public function __construct(private LaboratoryService $laboratoryService) {}

    public function tests(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        return LabTestResource::collection($this->laboratoryService->paginateTests($filters))
            ->additional([
                'success' => true,
                'message' => 'Laboratory tests retrieved successfully.',
            ]);
    }

    public function storeTest(StoreLabTestRequest $request): JsonResponse
    {
        $test = $this->laboratoryService->createTest($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Laboratory test created successfully.',
            'data' => (new LabTestResource($test))->resolve(),
        ], 201);
    }

    public function updateTest(UpdateLabTestRequest $request, LabTest $labTest): JsonResponse
    {
        $test = $this->laboratoryService->updateTest($labTest, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Laboratory test updated successfully.',
            'data' => (new LabTestResource($test))->resolve(),
        ]);
    }

    public function destroyTest(LabTest $labTest): JsonResponse
    {
        $this->laboratoryService->archiveTest($labTest);

        return response()->json([
            'success' => true,
            'message' => 'Laboratory test deactivated successfully.',
        ]);
    }

    public function requests(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:REQUESTED,SAMPLE_COLLECTED,PROCESSING,COMPLETED,CANCELLED'],
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'lab_test_id' => ['nullable', 'integer', 'exists:lab_tests,id'],
            'requested_on' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        return LabRequestResource::collection(
            $this->laboratoryService->paginateRequests($filters, Auth::guard('api')->user())
        )->additional([
            'success' => true,
            'message' => 'Laboratory requests retrieved successfully.',
        ]);
    }

    public function storeRequest(StoreLabRequestRequest $request): JsonResponse
    {
        $labRequest = $this->laboratoryService->createRequest($request->validated(), Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Laboratory request created successfully.',
            'data' => (new LabRequestResource($labRequest))->resolve(),
        ], 201);
    }

    public function showRequest(LabRequest $labRequest): JsonResponse
    {
        $user = Auth::guard('api')->user();
        $this->laboratoryService->ensureCanView($labRequest, $user);

        return response()->json([
            'success' => true,
            'message' => 'Laboratory request retrieved successfully.',
            'data' => (new LabRequestResource($this->laboratoryService->loadRequest($labRequest)))->resolve(),
        ]);
    }

    public function collectSample(CollectSampleRequest $request, LabRequest $labRequest): JsonResponse
    {
        $result = $this->laboratoryService->collectSample($labRequest, $request->validated(), Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Sample collection recorded successfully.',
            'data' => (new LabRequestResource($result))->resolve(),
        ]);
    }

    public function startProcessing(LabRequest $labRequest): JsonResponse
    {
        $result = $this->laboratoryService->startProcessing($labRequest);

        return response()->json([
            'success' => true,
            'message' => 'Laboratory test moved to processing.',
            'data' => (new LabRequestResource($result))->resolve(),
        ]);
    }

    public function saveResult(StoreLabResultRequest $request, LabRequest $labRequest): JsonResponse
    {
        $result = $this->laboratoryService->saveResult($labRequest, $request->validated(), Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Laboratory result saved successfully.',
            'data' => (new LabRequestResource($result))->resolve(),
        ]);
    }
}
