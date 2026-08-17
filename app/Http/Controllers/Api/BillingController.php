<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\StoreInvoiceRequest;
use App\Http\Requests\Billing\StorePaymentRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function __construct(private BillingService $billingService) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:UNPAID,PARTIALLY_PAID,PAID,CANCELLED'],
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        return InvoiceResource::collection($this->billingService->invoices($filters))
            ->additional(['success' => true, 'message' => 'Invoices retrieved successfully.']);
    }

    public function summary(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->billingService->summary()]);
    }

    public function patients(Request $request): JsonResponse
    {
        $data = $request->validate(['search' => ['nullable', 'string', 'max:255']]);

        return response()->json([
            'success' => true,
            'data' => $this->billingService->patients(trim((string) ($data['search'] ?? ''))),
        ]);
    }

    public function availableCharges(Request $request): JsonResponse
    {
        $data = $request->validate(['patient_id' => ['required', 'integer', 'exists:patients,id']]);

        return response()->json([
            'success' => true,
            'data' => $this->billingService->availableCharges((int) $data['patient_id']),
        ]);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->billingService->createInvoice($request->validated(), Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Invoice generated successfully.',
            'data' => (new InvoiceResource($invoice))->resolve(),
        ], 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => (new InvoiceResource($this->billingService->loadInvoice($invoice)))->resolve(),
        ]);
    }

    public function storePayment(StorePaymentRequest $request, Invoice $invoice): JsonResponse
    {
        $invoice = $this->billingService->recordPayment($invoice, $request->validated(), Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully.',
            'data' => (new InvoiceResource($invoice))->resolve(),
        ]);
    }

    public function cancel(Invoice $invoice): JsonResponse
    {
        $invoice = $this->billingService->cancelInvoice($invoice, Auth::guard('api')->user());

        return response()->json([
            'success' => true,
            'message' => 'Invoice cancelled successfully.',
            'data' => (new InvoiceResource($invoice))->resolve(),
        ]);
    }
}
