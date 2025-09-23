<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class CacheController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function index()
    {
        $stats = $this->cacheService->getStats();
        
        return Inertia::render('admin/CacheManagement', [
            'stats' => $stats,
            'keys' => $this->cacheService->getActiveKeys()
        ]);
    }

    public function clear(Request $request)
    {
        $type = $request->get('type', 'all');
        
        switch ($type) {
            case 'metrics':
                $this->cacheService->clearMetricsCache();
                break;
            case 'ai':
                $this->cacheService->clearAICache();
                break;
            case 'sessions':
                $this->cacheService->clearSessionCache();
                break;
            default:
                Cache::flush();
        }

        return response()->json(['success' => true, 'message' => 'Cache cleared']);
    }

    public function optimize()
    {
        $result = $this->cacheService->optimize();
        
        return response()->json([
            'success' => true,
            'optimized_keys' => $result['optimized'],
            'removed_keys' => $result['removed']
        ]);
    }

    public function getMetrics()
    {
        return response()->json($this->cacheService->getDetailedMetrics());
    }
}