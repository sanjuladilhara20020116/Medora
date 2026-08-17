<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\StoreMedicineCategoryRequest;
use App\Http\Requests\Pharmacy\StoreMedicineRequest;
use App\Http\Requests\Pharmacy\StoreMedicineStockRequest;
use App\Http\Requests\Pharmacy\StorePrescriptionDispenseRequest;
use App\Http\Resources\MedicineCategoryResource;
use App\Http\Resources\MedicineResource;
use App\Http\Resources\MedicineStockResource;
use App\Http\Resources\PharmacyPrescriptionResource;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\Prescription;
use App\Services\PharmacyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PharmacyController extends Controller
{
    public function __construct(private PharmacyService $pharmacyService) {}

    public function categories(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:active,inactive'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        return MedicineCategoryResource::collection($this->pharmacyService->categories($filters))
            ->additional([
                'success' => true,
                'message' => 'Medicine categories retrieved successfully.',
            ]);
    }

    public function storeCategory(StoreMedicineCategoryRequest $request): JsonResponse
    {
        $category = $this->pharmacyService->createCategory($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Medicine category created successfully.',
            'data' => (new MedicineCategoryResource($category))->resolve(),
        ], 201);
    }

    public function updateCategory(StoreMedicineCategoryRequest $request, MedicineCategory $medicineCategory): JsonResponse
    {
        $category = $this->pharmacyService->updateCategory($medicineCategory, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Medicine category updated successfully.',
            'data' => (new MedicineCategoryResource($category))->resolve(),
        ]);
    }

    public function destroyCategory(MedicineCategory $medicineCategory): JsonResponse
    {
        $this->pharmacyService->archiveCategory($medicineCategory);

        return response()->json([
            'success' => true,
            'message' => 'Medicine category deactivated successfully.',
        ]);
    }

    public function medicines(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
            'category_id' => ['nullable', 'integer', 'exists:medicine_categories,id'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        return MedicineResource::collection($this->pharmacyService->medicines($filters))
            ->additional([
                'success' => true,
                'message' => 'Medicines retrieved successfully.',
            ]);
    }

    public function storeMedicine(StoreMedicineRequest $request): JsonResponse
    {
        $medicine = $this->pharmacyService->createMedicine($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Medicine created successfully.',
            'data' => (new MedicineResource($medicine))->resolve(),
        ], 201);
    }

    public function updateMedicine(StoreMedicineRequest $request, Medicine $medicine): JsonResponse
    {
        $medicine = $this->pharmacyService->updateMedicine($medicine, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Medicine updated successfully.',
            'data' => (new MedicineResource($medicine))->resolve(),
        ]);
    }

    public function destroyMedicine(Medicine $medicine): JsonResponse
    {
        $this->pharmacyService->archiveMedicine($medicine);

        return response()->json([
            'success' => true,
            'message' => 'Medicine deactivated successfully.',
        ]);
    }

    public function stocks(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'medicine_id' => ['nullable', 'integer', 'exists:medicines,id'],
            'expiry_status' => ['nullable', 'in:expired,expiring,valid'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        return MedicineStockResource::collection($this->pharmacyService->stocks($filters))
            ->additional([
                'success' => true,
                'message' => 'Medicine stock retrieved successfully.',
            ]);
    }

    public function receiveStock(StoreMedicineStockRequest $request): JsonResponse
    {
        $stock = $this->pharmacyService->receiveStock($request->validated(), Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Medicine stock received successfully.',
            'data' => (new MedicineStockResource($stock))->resolve(),
        ], 201);
    }

    public function alerts(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Pharmacy stock alerts retrieved successfully.',
            'data' => $this->pharmacyService->alerts(),
        ]);
    }

    public function prescriptions(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        return PharmacyPrescriptionResource::collection($this->pharmacyService->prescriptions($filters))
            ->additional([
                'success' => true,
                'message' => 'Prescriptions retrieved successfully.',
            ]);
    }

    public function showPrescription(Prescription $prescription): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Prescription retrieved successfully.',
            'data' => (new PharmacyPrescriptionResource($this->pharmacyService->loadPrescription($prescription)))->resolve(),
        ]);
    }

    public function dispensePrescription(StorePrescriptionDispenseRequest $request, Prescription $prescription): JsonResponse
    {
        $prescription = $this->pharmacyService->dispense($prescription, $request->validated(), Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Prescription dispensed successfully.',
            'data' => (new PharmacyPrescriptionResource($prescription))->resolve(),
        ]);
    }
}
