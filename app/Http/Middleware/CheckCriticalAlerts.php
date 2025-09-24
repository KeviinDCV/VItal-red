<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CriticalAlert;
use App\Services\CriticalAlertService;

class CheckCriticalAlerts
{
    protected $alertService;

    public function __construct(CriticalAlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Verificar alertas críticas pendientes
        $criticalAlerts = CriticalAlert::where('status', 'pending')
            ->where('priority', 'CRITICAL')
            ->where('created_at', '<', now()->subMinutes(15))
            ->count();

        if ($criticalAlerts > 0) {
            $request->merge(['critical_alerts_count' => $criticalAlerts]);
        }

        // Ejecutar verificaciones del sistema
        $this->alertService->checkCriticalCases();
        $this->alertService->checkSystemHealth();

        return $next($request);
    }
}
