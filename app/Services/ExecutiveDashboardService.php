<?php

namespace App\Services;

use App\Models\SolicitudReferencia;
use App\Models\DecisionReferencia;
use App\Models\CriticalAlert;
use App\Models\AutomaticResponse;
use App\Models\SystemMetrics;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExecutiveDashboardService
{
    public function getRealTimeMetrics()
    {
        return Cache::remember('executive_metrics', 300, function () {
            $today = Carbon::today();
            $thisWeek = Carbon::now()->startOfWeek();
            $thisMonth = Carbon::now()->startOfMonth();

            return [
                'solicitudes_hoy' => SolicitudReferencia::whereDate('created_at', $today)->count(),
                'casos_rojos_pendientes' => SolicitudReferencia::where('prioridad_ia', 'ROJO')
                    ->whereNull('decision_id')->count(),
                'casos_verdes_automaticos' => AutomaticResponse::whereDate('sent_at', $today)->count(),
                'tiempo_promedio_respuesta' => $this->getAverageResponseTime(),
                'eficiencia_ia' => $this->getAIEfficiency(),
                'alertas_criticas' => CriticalAlert::active()->count(),
                'uptime_sistema' => $this->getSystemUptime(),
                'usuarios_activos' => $this->getActiveUsers(),
                'tendencias' => $this->getTrendData(),
                'especialidades_demandadas' => $this->getTopSpecialties(),
                'ips_mas_activas' => $this->getTopIPS(),
                'performance_score' => $this->getPerformanceScore()
            ];
        });
    }

    public function getCriticalAlerts()
    {
        return CriticalAlert::active()
            ->critical()
            ->with(['assignedUser', 'source'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($alert) {
                return [
                    'id' => $alert->id,
                    'title' => $alert->title,
                    'message' => $alert->message,
                    'priority' => $alert->priority,
                    'time_elapsed' => $alert->getTimeElapsed(),
                    'assigned_to' => $alert->assignedUser?->name,
                    'action_required' => $alert->action_required,
                    'action_url' => $alert->action_url,
                    'should_escalate' => $alert->shouldEscalate()
                ];
            });
    }

    public function getTrends()
    {
        $days = 30;
        $trends = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'solicitudes' => SolicitudReferencia::whereDate('created_at', $date)->count(),
                'respuestas_automaticas' => AutomaticResponse::whereDate('sent_at', $date)->count(),
                'casos_rojos' => SolicitudReferencia::where('prioridad_ia', 'ROJO')
                    ->whereDate('created_at', $date)->count(),
                'tiempo_respuesta' => $this->getAverageResponseTimeForDate($date)
            ];
        }

        return $trends;
    }

    public function getPerformanceData($period = '24h')
    {
        switch ($period) {
            case '1h':
                return $this->getHourlyPerformance();
            case '24h':
                return $this->getDailyPerformance();
            case '7d':
                return $this->getWeeklyPerformance();
            case '30d':
                return $this->getMonthlyPerformance();
            default:
                return $this->getDailyPerformance();
        }
    }

    private function getAverageResponseTime()
    {
        $avgSeconds = DecisionReferencia::whereNotNull('created_at')
            ->join('solicitudes_referencia', 'decisiones_referencia.solicitud_id', '=', 'solicitudes_referencia.id')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, solicitudes_referencia.created_at, decisiones_referencia.created_at)) as avg_time')
            ->value('avg_time');

        return $avgSeconds ? round($avgSeconds / 3600, 2) : 0; // Convertir a horas
    }

    private function getAIEfficiency()
    {
        $total = SolicitudReferencia::whereNotNull('prioridad_ia')->count();
        if ($total === 0) return 0;

        $correct = DecisionReferencia::join('solicitudes_referencia', 'decisiones_referencia.solicitud_id', '=', 'solicitudes_referencia.id')
            ->where(function ($query) {
                $query->where('solicitudes_referencia.prioridad_ia', 'ROJO')
                      ->where('decisiones_referencia.decision', 'aceptada')
                      ->orWhere('solicitudes_referencia.prioridad_ia', 'VERDE')
                      ->where('decisiones_referencia.decision', 'rechazada');
            })
            ->count();

        return round(($correct / $total) * 100, 2);
    }

    private function getSystemUptime()
    {
        // Simulación - en producción se conectaría con sistema de monitoreo
        return 99.95;
    }

    private function getActiveUsers()
    {
        return Cache::remember('active_users_count', 600, function () {
            return DB::table('sessions')
                ->where('last_activity', '>', Carbon::now()->subMinutes(30)->timestamp)
                ->count();
        });
    }

    private function getTrendData()
    {
        return [
            'solicitudes_trend' => $this->calculateTrend('solicitudes', 7),
            'respuestas_trend' => $this->calculateTrend('respuestas', 7),
            'eficiencia_trend' => $this->calculateTrend('eficiencia', 7)
        ];
    }

    private function getTopSpecialties()
    {
        return SolicitudReferencia::select('especialidad_solicitada', DB::raw('COUNT(*) as count'))
            ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('especialidad_solicitada')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();
    }

    private function getTopIPS()
    {
        return SolicitudReferencia::join('users', 'solicitudes_referencia.solicitante_id', '=', 'users.id')
            ->select('users.name', DB::raw('COUNT(*) as count'))
            ->where('users.role', 'ips')
            ->whereDate('solicitudes_referencia.created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('users.id', 'users.name')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();
    }

    private function getPerformanceScore()
    {
        $metrics = [
            'response_time' => min(100, (2 / max($this->getAverageResponseTime(), 0.1)) * 100),
            'ai_efficiency' => $this->getAIEfficiency(),
            'uptime' => $this->getSystemUptime(),
            'alert_resolution' => $this->getAlertResolutionRate()
        ];

        return round(array_sum($metrics) / count($metrics), 2);
    }

    private function getAlertResolutionRate()
    {
        $total = CriticalAlert::whereDate('created_at', '>=', Carbon::now()->subDays(7))->count();
        if ($total === 0) return 100;

        $resolved = CriticalAlert::where('status', 'resolved')
            ->whereDate('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        return round(($resolved / $total) * 100, 2);
    }

    private function calculateTrend($metric, $days)
    {
        // Implementar cálculo de tendencia
        return rand(-5, 15); // Simulación
    }

    private function getAverageResponseTimeForDate($date)
    {
        return DecisionReferencia::whereDate('created_at', $date)
            ->join('solicitudes_referencia', 'decisiones_referencia.solicitud_id', '=', 'solicitudes_referencia.id')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, solicitudes_referencia.created_at, decisiones_referencia.created_at)) as avg_time')
            ->value('avg_time') ?? 0;
    }

    public function exportExecutiveReport($format = 'excel')
    {
        $data = $this->getRealTimeMetrics();
        
        // Implementar exportación según formato
        return response()->json(['message' => 'Reporte generado', 'data' => $data]);
    }
}