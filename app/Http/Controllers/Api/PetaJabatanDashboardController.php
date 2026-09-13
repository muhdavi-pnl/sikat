<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PetaJabatan\PetaJabatanDashboardService;

class PetaJabatanDashboardController extends Controller
{
    public function index(PetaJabatanDashboardService $dashboardService)
    {
        return response()->json([
            'data' => $dashboardService->summary(),
        ]);
    }
}
