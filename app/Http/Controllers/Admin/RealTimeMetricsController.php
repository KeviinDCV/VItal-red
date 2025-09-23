<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemMetrics;
use App\Services\ExecutiveDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RealTimeMetricsController extends Controller
{
    protected $dashboardService;

    public function __construct(ExecutiveDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        return Inertia::render('admin/RealTimeMetrics');
    }

    public function getData()
    {
        return response()->json([
            'metrics' => $this->dashboardService->getRealTimeMetrics(),
            'system_health' => $this->getSystemHealth()
        ]);
    }

    private function getSystemHealth()
    {
        return [
            'cpu_usage' => SystemMetrics::getLatestValue('cpu_usage') ?? 25,
            'memory_usage' => SystemMetrics::getLatestValue('memory_usage') ?? 45,
            'disk_usage' => SystemMetrics::getLatestValue('disk_usage') ?? 65,
            'network_latency' => SystemMetrics::getLatestValue('network_latency') ?? 35,
            'active_connections' => SystemMetrics::getLatestValue('active_connections') ?? 75,
            'queue_size' => SystemMetrics::getLatestValue('queue_size') ?? 5
        ];
    }
}