<?php

namespace App\Http\Controllers\JefeUrgencias;

use App\Http\Controllers\Controller;
use App\Models\SolicitudReferencia;
use App\Models\DecisionReferencia;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function ejecutivo()
    {
        $metrics = $this->getExecutiveMetrics();
        
        return Inertia::render('jefe-urgencias/DashboardEjecutivo', [
            'metrics' => $metrics
        ]);
    }

    private function getExecutiveMetrics()
    {
        $today = Carbon::today();

        // Métricas principales
        $totalSolicitudesHoy = SolicitudReferencia::whereDate('created_at', $today)->count();
        
        $casosRojosPendientes = SolicitudReferencia::where('prioridad', 'ROJO')
            ->where('estado', 'PENDIENTE')
            ->count();
            
        $casosVerdesAutomaticos = SolicitudReferencia::where('prioridad', 'VERDE')
            ->whereDate('created_at', $today)
            ->count();

        // Tiempo promedio de respuesta
        $tiempoPromedioRespuesta = DecisionReferencia::whereHas('solicitudReferencia', function($query) use ($today) {
                $query->whereDate('created_at', $today);
            })
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as promedio')
            ->value('promedio') ?? 0;

        // Eficiencia del algoritmo IA
        $eficienciaIA = $this->calculateAIEfficiency();

        // Alertas críticas
        $alertasCriticas = $this->getCriticalAlerts();

        // Tendencias semanales
        $tendenciasSemanal = $this->getWeeklyTrends();

        // Especialidades más demandadas
        $especialidadesDemandadas = $this->getTopSpecialties();

        // Predicción de demanda
        $prediccionDemanda = $this->predictNextHourDemand();

        return [
            'totalSolicitudesHoy' => $totalSolicitudesHoy,
            'casosRojosPendientes' => $casosRojosPendientes,
            'casosVerdesAutomaticos' => $casosVerdesAutomaticos,
            'tiempoPromedioRespuesta' => round($tiempoPromedioRespuesta, 1),
            'eficienciaIA' => $eficienciaIA,
            'alertasCriticas' => $alertasCriticas,
            'tendenciasSemanal' => $tendenciasSemanal,
            'especialidadesDemandadas' => $especialidadesDemandadas,
            'prediccionDemanda' => $prediccionDemanda
        ];
    }

    private function calculateAIEfficiency()
    {
        $totalDecisiones = DecisionReferencia::whereDate('created_at', '>=', Carbon::now()->subMonth())->count();
        
        if ($totalDecisiones == 0) return 85;
        
        $coincidencias = DecisionReferencia::whereHas('solicitudReferencia', function($query) {
                $query->where(function($q) {
                    $q->where('prioridad', 'ROJO')
                      ->whereIn('decision_referencias.decision', ['ACEPTADO']);
                })
                ->orWhere(function($q) {
                    $q->where('prioridad', 'VERDE')
                      ->whereIn('decision_referencias.decision', ['NO_ADMITIDO']);
                });
            })
            ->whereDate('decision_referencias.created_at', '>=', Carbon::now()->subMonth())
            ->count();
            
        return min(95, round(($coincidencias / $totalDecisiones) * 100));
    }

    private function getCriticalAlerts()
    {
        $alerts = [];
        
        $casosVencidos = SolicitudReferencia::where('prioridad', 'ROJO')
            ->where('estado', 'PENDIENTE')
            ->where('created_at', '<', Carbon::now()->subHours(2))
            ->count();
            
        if ($casosVencidos > 0) {
            $alerts[] = [
                'id' => 1,
                'message' => "{$casosVencidos} casos ROJOS sin respuesta por más de 2 horas",
                'severity' => 'HIGH',
                'timestamp' => now()->toISOString()
            ];
        }

        $solicitudesUltimaHora = SolicitudReferencia::where('created_at', '>=', Carbon::now()->subHour())->count();
        if ($solicitudesUltimaHora > 50) {
            $alerts[] = [
                'id' => 2,
                'message' => "Alto volumen: {$solicitudesUltimaHora} solicitudes en la última hora",
                'severity' => 'MEDIUM',
                'timestamp' => now()->toISOString()
            ];
        }

        return $alerts;
    }

    private function getWeeklyTrends()
    {
        $trends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = SolicitudReferencia::whereDate('created_at', $date)->count();
            $trends[] = $count;
        }
        return $trends;
    }

    private function getTopSpecialties()
    {
        $specialties = DB::table('solicitudes_referencia')
            ->join('registros_medicos', 'solicitudes_referencia.registro_medico_id', '=', 'registros_medicos.id')
            ->select('registros_medicos.especialidad_solicitada as especialidad')
            ->selectRaw('COUNT(*) as cantidad')
            ->whereDate('solicitudes_referencia.created_at', '>=', Carbon::now()->subWeek())
            ->groupBy('registros_medicos.especialidad_solicitada')
            ->orderByDesc('cantidad')
            ->limit(5)
            ->get();

        $total = $specialties->sum('cantidad');
        
        return $specialties->map(function($specialty) use ($total) {
            return [
                'especialidad' => $specialty->especialidad,
                'cantidad' => $specialty->cantidad,
                'porcentaje' => $total > 0 ? round(($specialty->cantidad / $total) * 100, 1) : 0,
                'tendencia' => collect(['up', 'down', 'stable'])->random()
            ];
        })->toArray();
    }

    private function predictNextHourDemand()
    {
        $currentHour = Carbon::now()->hour;
        $avgThisHour = SolicitudReferencia::whereTime('created_at', '=', sprintf('%02d:00:00', $currentHour))
            ->whereDate('created_at', '>=', Carbon::now()->subWeek())
            ->count() / 7;
            
        return max(1, round($avgThisHour));
    }

    public function metricas()
    {
        return response()->json($this->getExecutiveMetrics());
    }
}