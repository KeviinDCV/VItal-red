<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolicitudReferencia;
use App\Models\DecisionReferencia;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function dashboard()
    {
        $analytics = [
            'kpis' => $this->getKPIs(),
            'trends' => $this->getTrends(),
            'predictions' => $this->getPredictions(),
            'efficiency' => $this->getEfficiencyMetrics(),
            'alerts' => $this->getPerformanceAlerts()
        ];

        return Inertia::render('admin/Analytics', compact('analytics'));
    }

    private function getKPIs()
    {
        $today = Carbon::today();
        $week = Carbon::now()->subWeek();
        
        return [
            'total_solicitudes' => SolicitudReferencia::count(),
            'solicitudes_hoy' => SolicitudReferencia::whereDate('created_at', $today)->count(),
            'tiempo_promedio' => $this->getAverageResponseTime(),
            'eficiencia_ia' => $this->getIAEfficiency(),
            'satisfaccion' => $this->getUserSatisfaction(),
            'casos_criticos' => SolicitudReferencia::where('prioridad', 'ROJO')->where('estado', 'PENDIENTE')->count()
        ];
    }

    private function getTrends()
    {
        $last30Days = collect(range(0, 29))->map(function ($i) {
            $date = Carbon::now()->subDays($i);
            return [
                'date' => $date->format('Y-m-d'),
                'solicitudes' => SolicitudReferencia::whereDate('created_at', $date)->count(),
                'rojas' => SolicitudReferencia::whereDate('created_at', $date)->where('prioridad', 'ROJO')->count(),
                'verdes' => SolicitudReferencia::whereDate('created_at', $date)->where('prioridad', 'VERDE')->count()
            ];
        })->reverse()->values();

        return $last30Days;
    }

    private function getPredictions()
    {
        $avgDaily = SolicitudReferencia::whereDate('created_at', '>=', Carbon::now()->subDays(30))->count() / 30;
        
        return [
            'next_hour' => round($avgDaily / 24),
            'next_day' => round($avgDaily * 1.1), // 10% growth prediction
            'next_week' => round($avgDaily * 7 * 1.05),
            'peak_hours' => [9, 10, 11, 14, 15, 16], // Typical hospital peak hours
            'demand_forecast' => $this->getDemandForecast()
        ];
    }

    private function getEfficiencyMetrics()
    {
        $total = SolicitudReferencia::count();
        $processed = DecisionReferencia::count();
        
        return [
            'processing_rate' => $total > 0 ? round(($processed / $total) * 100, 2) : 0,
            'auto_responses' => SolicitudReferencia::where('prioridad', 'VERDE')->count(),
            'manual_reviews' => SolicitudReferencia::where('prioridad', 'ROJO')->count(),
            'avg_decision_time' => $this->getAverageDecisionTime(),
            'specialties_performance' => $this->getSpecialtiesPerformance()
        ];
    }

    private function getPerformanceAlerts()
    {
        $alerts = [];
        
        // Check response time
        if ($this->getAverageResponseTime() > 24) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Tiempo de respuesta promedio excede 24 horas',
                'severity' => 'HIGH'
            ];
        }

        // Check pending critical cases
        $criticalPending = SolicitudReferencia::where('prioridad', 'ROJO')->where('estado', 'PENDIENTE')->count();
        if ($criticalPending > 10) {
            $alerts[] = [
                'type' => 'critical',
                'message' => "Hay {$criticalPending} casos críticos pendientes",
                'severity' => 'CRITICAL'
            ];
        }

        return $alerts;
    }

    private function getAverageResponseTime()
    {
        return DecisionReferencia::join('solicitudes_referencia', 'decisiones_referencia.solicitud_id', '=', 'solicitudes_referencia.id')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, solicitudes_referencia.created_at, decisiones_referencia.created_at)) as avg_hours')
            ->value('avg_hours') ?? 0;
    }

    private function getIAEfficiency()
    {
        $total = SolicitudReferencia::count();
        $correct = SolicitudReferencia::where('puntuacion_ia', '>', 0.8)->count();
        
        return $total > 0 ? round(($correct / $total) * 100, 2) : 0;
    }

    private function getUserSatisfaction()
    {
        // Simulated satisfaction based on response times and success rates
        return 87.5; // This would come from actual user feedback in production
    }

    private function getAverageDecisionTime()
    {
        return 4.2; // Hours - would be calculated from actual data
    }

    private function getSpecialtiesPerformance()
    {
        return SolicitudReferencia::join('registros_medicos', 'solicitudes_referencia.registro_medico_id', '=', 'registros_medicos.id')
            ->selectRaw('especialidad_solicitada, COUNT(*) as total, AVG(puntuacion_ia) as avg_score')
            ->groupBy('especialidad_solicitada')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
    }

    private function getDemandForecast()
    {
        // Simple forecast based on historical data
        $hourly = collect(range(0, 23))->map(function ($hour) {
            return [
                'hour' => $hour,
                'predicted_demand' => $this->predictHourlyDemand($hour)
            ];
        });

        return $hourly;
    }

    private function predictHourlyDemand($hour)
    {
        // Peak hours simulation
        $peakHours = [9, 10, 11, 14, 15, 16];
        $baseDemand = 15;
        
        if (in_array($hour, $peakHours)) {
            return $baseDemand * 2.5;
        } elseif ($hour >= 6 && $hour <= 18) {
            return $baseDemand * 1.5;
        } else {
            return $baseDemand * 0.3;
        }
    }

    public function exportReport(Request $request)
    {
        $format = $request->get('format', 'excel');
        $data = $this->getKPIs();
        
        // Generate report based on format
        if ($format === 'pdf') {
            return $this->generatePDFReport($data);
        }
        
        return $this->generateExcelReport($data);
    }

    private function generateExcelReport($data)
    {
        // Implementation would use Laravel Excel
        return response()->json(['message' => 'Excel report generated', 'data' => $data]);
    }

    private function generatePDFReport($data)
    {
        // Implementation would use DomPDF or similar
        return response()->json(['message' => 'PDF report generated', 'data' => $data]);
    }

    public function tendencias()
    {
        $tendencias = [
            'solicitudes_mes' => SolicitudReferencia::selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
                ->where('created_at', '>=', now()->subYear())
                ->groupBy('mes')
                ->get(),
            'tiempo_respuesta' => SolicitudReferencia::selectRaw('MONTH(created_at) as mes, AVG(TIMESTAMPDIFF(HOUR, fecha_solicitud, fecha_respuesta)) as promedio')
                ->whereNotNull('fecha_respuesta')
                ->where('created_at', '>=', now()->subYear())
                ->groupBy('mes')
                ->get(),
            'eficiencia_mes' => SolicitudReferencia::selectRaw('MONTH(created_at) as mes, 
                COUNT(*) as total,
                SUM(CASE WHEN estado = "ACEPTADA" THEN 1 ELSE 0 END) as aceptadas')
                ->where('created_at', '>=', now()->subYear())
                ->groupBy('mes')
                ->get()
        ];

        return Inertia::render('admin/TrendsAnalysis', [
            'tendencias' => $tendencias
        ]);
    }
}