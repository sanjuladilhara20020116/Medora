<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {
    }

    /**
     * Return the administrator dashboard statistics.
     */
    public function admin(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Admin dashboard data retrieved successfully.',

            'data' => $this->dashboardService->getAdminDashboard(),
        ]);
    }
}