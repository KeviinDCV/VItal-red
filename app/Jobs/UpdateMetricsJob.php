<?php

namespace App\Jobs;

use App\Models\SystemMetrics;
use App\Models\SolicitudReferencia;
use App\Models\AutomaticResponse;
use App\Models\CriticalAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class UpdateMetricsJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $timestamp = now();
        
        // Métricas de solicitudes
        SystemMetrics::recordMetric(
            'solicitudes_total',
            SolicitudReferencia::count(),
            'gauge',
            'solicitudes'
        );
        
        SystemMetrics::recordMetric(
            'solicitudes_hoy',
            SolicitudReferencia::whereDate('created_at', today())->count(),
            'gauge',
            'solicitudes'
        );
        
        // Métricas de respuestas automáticas
        SystemMetrics::recordMetric(
            'respuestas_automaticas_hoy',
            AutomaticResponse::whereDate('sent_at', today())->count(),
            'gauge',
            'respuestas'
        );
        
        // Métricas de alertas críticas
        SystemMetrics::recordMetric(
            'alertas_criticas_activas',
            CriticalAlert::active()->count(),
            'gauge',
            'alertas'
        );
        
        // Métricas de casos pendientes
        SystemMetrics::recordMetric(
            'casos_rojos_pendientes',
            SolicitudReferencia::where('prioridad_ia', 'ROJO')
                ->whereNull('decision_id')->count(),
            'gauge',
            'casos'
        );
        
        // Métricas de tiempo de respuesta
        $avgResponseTime = DB::table('solicitudes_referencia')
            ->join('decisiones_referencia', 'solicitudes_referencia.id', '=', 'decisiones_referencia.solicitud_id')
            ->whereDate('solicitudes_referencia.created_at', today())
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, solicitudes_referencia.created_at, decisiones_referencia.created_at)) as avg_time')
            ->value('avg_time') ?? 0;
            
        SystemMetrics::recordMetric(
            'tiempo_promedio_respuesta',
            $avgResponseTime,
            'gauge',
            'performance'
        );
        
        // Métricas de usuarios activos
        $activeUsers = DB::table('sessions')
            ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
            ->count();
            
        SystemMetrics::recordMetric(
            'usuarios_activos',
            $activeUsers,
            'gauge',
            'usuarios'
        );
    }
}
