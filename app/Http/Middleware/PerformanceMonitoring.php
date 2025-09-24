<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MonitoringService;

class PerformanceMonitoring
{
    public function __construct(private MonitoringService $monitoring) {}

    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        
        $response = $next($request);
        
        $duration = (microtime(true) - $start) * 1000;
        
        $this->monitoring->logPerformanceMetric(
            $request->route()?->getName() ?? $request->path(),
            $duration,
            [
                'method' => $request->method(),
                'url' => $request->url(),
                'user_id' => auth()->id(),
                'status_code' => $response->getStatusCode(),
                'memory_usage' => memory_get_peak_usage(true)
            ]
        );
        
        return $response;
    }
}