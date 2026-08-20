<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function overview(Request $request): JsonResponse
    {
        return $this->response($this->reportService->overview($this->range($request)));
    }

    public function patients(Request $request): JsonResponse
    {
        return $this->response($this->reportService->patients($this->range($request)));
    }

    public function appointments(Request $request): JsonResponse
    {
        return $this->response($this->reportService->appointments($this->range($request)));
    }

    public function revenue(Request $request): JsonResponse
    {
        return $this->response($this->reportService->revenue($this->range($request)));
    }

    public function pharmacy(Request $request): JsonResponse
    {
        return $this->response($this->reportService->pharmacy($this->range($request)));
    }

    public function laboratory(Request $request): JsonResponse
    {
        return $this->response($this->reportService->laboratory($this->range($request)));
    }

    public function staff(Request $request): JsonResponse
    {
        return $this->response($this->reportService->staff($this->range($request)));
    }

    private function range(Request $request): array
    {
        return $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
        ]);
    }

    private function response(array $data): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data]);
    }
}
