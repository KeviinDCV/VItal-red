<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseOptimizationService;
use App\Services\CacheService;
use App\Services\MonitoringService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PerformanceController extends Controller
{
    public function __construct(
        private DatabaseOptimizationService $dbService,
        private CacheService $cacheService,
        private MonitoringService $monitoringService
    ) {}

    public function dashboard()
    {
        $performance = [
            'system_health' => $this->monitoringService->checkSystemHealth(),
            'cache_stats' => $this->cacheService->getCacheStats(),
            'db_performance' => $this->dbService->analyzePerformance(),
            'system_metrics' => $this->monitoringService->getSystemMetrics()
        ];

        return Inertia::render('admin/Performance', compact('performance'));
    }

    public function optimizeDatabase()
    {
        $results = [
            'optimized_queries' => $this->dbService->optimizeQueries(),
            'cleaned_records' => $this->dbService->cleanOldData(),
            'timestamp' => now()
        ];

        return response()->json([
            'success' => true,
            'message' => 'Optimización de base de datos completada',
            'data' => $results
        ]);
    }

    public function clearCache(Request $request)
    {
        $type = $request->get('type', 'all');
        
        switch ($type) {
            case 'dashboard':
                $this->cacheService->invalidateDashboardCache();
                break;
            case 'user':
                $userId = $request->get('user_id');
                if ($userId) {
                    $this->cacheService->invalidateUserCache($userId);
                }
                break;
            case 'all':
            default:
                cache()->flush();
                break;
        }

        return response()->json([
            'success' => true,
            'message' => "Cache {$type} limpiado exitosamente"
        ]);
    }

    public function getMetrics()
    {
        return response()->json([
            'metrics' => $this->monitoringService->getSystemMetrics(),
            'timestamp' => now()
        ]);
    }
}