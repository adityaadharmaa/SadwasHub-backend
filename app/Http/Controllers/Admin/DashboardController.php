<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $analyticsData = $this->dashboardService->getAnalytics();

        return $this->successResponse(
            $analyticsData,
            'Data statistik Sadewas Hub berhasil diambil.'
        );
    }
}
