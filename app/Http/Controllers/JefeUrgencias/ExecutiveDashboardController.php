<?php

namespace App\Http\Controllers\JefeUrgencias;

use App\Http\Controllers\Controller;
use App\Models\SolicitudReferencia;
use App\Models\DecisionReferencia;
use App\Models\SystemMetrics;
use App\Services\ExecutiveDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ExecutiveDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(ExecutiveDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $metrics = $this->dashboardService->getRealTimeMetrics();
        
        return Inertia::render('jefe-urgencias/ExecutiveDashboard', [
            'metrics' => $metrics,
            'alerts' => $this->dashboardService->getCriticalAlerts(),
            'trends' => $this->dashboardService->getTrends()
        ]);
    }

    public function getRealTimeMetrics()
    {
        return response()->json($this->dashboardService->getRealTimeMetrics());
    }

    public function getCriticalAlerts()
    {
        return response()->json($this->dashboardService->getCriticalAlerts());
    }

    public function getPerformanceData(Request $request)
    {
        $period = $request->get('period', '24h');
        return response()->json($this->dashboardService->getPerformanceData($period));
    }

    public function exportReport(Request $request)
    {
        $format = $request->get('format', 'excel');
        return $this->dashboardService->exportExecutiveReport($format);
    }
}