<?php

namespace App\Http\Controllers;

use App\Models\SolicitudReferencia;
use App\Models\DecisionReferencia;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class TrendsAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $periodo = $request->get('periodo', 90); // 3 meses por defecto
        
        $trends = [
            'volumen_solicitudes' => $this->getVolumenTrend($periodo),
            'tiempo_respuesta' => $this->getTiempoRespuestaTrend($periodo),
            'especialidades_demandadas' => $this->getEspecialidadesTrend($periodo),
            'patrones_temporales' => $this->getPatronesTemporales($periodo),
            'predicciones' => $this->getPredictions($periodo)
        ];

        return Inertia::render('admin/TrendsAnalysis', [
            'tendencias' => $trends,
            'periodo' => $periodo
        ]);
    }

    private function getVolumenTrend($periodo)
    {
        $data = SolicitudReferencia::selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays($periodo))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Calcular tendencia (regresión lineal simple)
        $trend = $this->calculateLinearTrend($data->pluck('total')->toArray());
        
        return [
            'data' => $data,
            'tendencia' => $trend,
            'crecimiento_porcentual' => $this->calculateGrowthRate($data)
        ];
    }

    private function getTiempoRespuestaTrend($periodo)
    {
        $data = DecisionReferencia::join('solicitudes_referencia', 'decisiones_referencia.solicitud_referencia_id', '=', 'solicitudes_referencia.id')
            ->selectRaw('DATE(decisiones_referencia.created_at) as fecha, 
                AVG(TIMESTAMPDIFF(HOUR, solicitudes_referencia.created_at, decisiones_referencia.created_at)) as tiempo_promedio')
            ->where('decisiones_referencia.created_at', '>=', now()->subDays($periodo))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return [
            'data' => $data,
            'tendencia' => $this->calculateLinearTrend($data->pluck('tiempo_promedio')->toArray()),
            'objetivo' => 24, // 24 horas objetivo
            'cumplimiento' => $data->where('tiempo_promedio', '<=', 24)->count() / max($data->count(), 1) * 100
        ];
    }

    private function getEspecialidadesTrend($periodo)
    {
        $current = SolicitudReferencia::join('registros_medicos', 'solicitudes_referencia.registro_medico_id', '=', 'registros_medicos.id')
            ->selectRaw('registros_medicos.especialidad_solicitada, COUNT(*) as total')
            ->where('solicitudes_referencia.created_at', '>=', now()->subDays($periodo/2))
            ->groupBy('registros_medicos.especialidad_solicitada')
            ->orderBy('total', 'desc')
            ->get();

        $previous = SolicitudReferencia::join('registros_medicos', 'solicitudes_referencia.registro_medico_id', '=', 'registros_medicos.id')
            ->selectRaw('registros_medicos.especialidad_solicitada, COUNT(*) as total')
            ->where('solicitudes_referencia.created_at', '>=', now()->subDays($periodo))
            ->where('solicitudes_referencia.created_at', '<', now()->subDays($periodo/2))
            ->groupBy('registros_medicos.especialidad_solicitada')
            ->orderBy('total', 'desc')
            ->get();

        return [
            'actual' => $current,
            'anterior' => $previous,
            'cambios' => $this->calculateSpecialtyChanges($current, $previous)
        ];
    }

    private function getPatronesTemporales($periodo)
    {
        return [
            'por_dia_semana' => SolicitudReferencia::selectRaw('DAYOFWEEK(created_at) as dia_semana, COUNT(*) as total')
                ->where('created_at', '>=', now()->subDays($periodo))
                ->groupBy('dia_semana')
                ->orderBy('dia_semana')
                ->get(),
                
            'por_hora' => SolicitudReferencia::selectRaw('HOUR(created_at) as hora, COUNT(*) as total')
                ->where('created_at', '>=', now()->subDays($periodo))
                ->groupBy('hora')
                ->orderBy('hora')
                ->get(),
                
            'por_mes' => SolicitudReferencia::selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
                ->where('created_at', '>=', now()->subDays($periodo))
                ->groupBy('mes')
                ->orderBy('mes')
                ->get()
        ];
    }

    private function getPredictions($periodo)
    {
        $historicalData = SolicitudReferencia::selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays($periodo))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $trend = $this->calculateLinearTrend($historicalData->pluck('total')->toArray());
        
        // Predicción simple para los próximos 30 días
        $predictions = [];
        for ($i = 1; $i <= 30; $i++) {
            $predictions[] = [
                'fecha' => now()->addDays($i)->format('Y-m-d'),
                'prediccion' => max(0, round($trend['slope'] * ($historicalData->count() + $i) + $trend['intercept']))
            ];
        }

        return [
            'proximos_30_dias' => $predictions,
            'confianza' => $trend['r_squared'],
            'tendencia_general' => $trend['slope'] > 0 ? 'creciente' : 'decreciente'
        ];
    }

    private function calculateLinearTrend($data)
    {
        $n = count($data);
        if ($n < 2) return ['slope' => 0, 'intercept' => 0, 'r_squared' => 0];

        $x = range(1, $n);
        $sumX = array_sum($x);
        $sumY = array_sum($data);
        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $data[$i];
            $sumX2 += $x[$i] * $x[$i];
            $sumY2 += $data[$i] * $data[$i];
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        // Calcular R²
        $meanY = $sumY / $n;
        $ssRes = 0;
        $ssTot = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $predicted = $slope * $x[$i] + $intercept;
            $ssRes += pow($data[$i] - $predicted, 2);
            $ssTot += pow($data[$i] - $meanY, 2);
        }
        
        $rSquared = $ssTot > 0 ? 1 - ($ssRes / $ssTot) : 0;

        return [
            'slope' => $slope,
            'intercept' => $intercept,
            'r_squared' => $rSquared
        ];
    }

    private function calculateGrowthRate($data)
    {
        if ($data->count() < 2) return 0;
        
        $first = $data->first()->total;
        $last = $data->last()->total;
        
        return $first > 0 ? (($last - $first) / $first) * 100 : 0;
    }

    private function calculateSpecialtyChanges($current, $previous)
    {
        $changes = [];
        
        foreach ($current as $specialty) {
            $previousCount = $previous->where('especialidad_solicitada', $specialty->especialidad_solicitada)->first()->total ?? 0;
            $change = $previousCount > 0 ? (($specialty->total - $previousCount) / $previousCount) * 100 : 100;
            
            $changes[] = [
                'especialidad' => $specialty->especialidad_solicitada,
                'actual' => $specialty->total,
                'anterior' => $previousCount,
                'cambio_porcentual' => $change
            ];
        }
        
        return collect($changes)->sortByDesc('cambio_porcentual')->values();
    }
}