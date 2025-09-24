<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DatabaseOptimizationService
{
    public function optimizeQueries()
    {
        // Optimizar consultas frecuentes con eager loading
        return [
            'solicitudes_with_relations' => $this->getSolicitudesOptimized(),
            'dashboard_metrics' => $this->getDashboardMetricsOptimized(),
            'notifications_optimized' => $this->getNotificationsOptimized()
        ];
    }

    private function getSolicitudesOptimized()
    {
        return DB::table('solicitudes_referencia as sr')
            ->select([
                'sr.id', 'sr.codigo_solicitud', 'sr.prioridad', 'sr.estado',
                'sr.created_at', 'sr.puntuacion_ia',
                'rm.nombre', 'rm.apellidos', 'rm.numero_identificacion',
                'rm.especialidad_solicitada'
            ])
            ->join('registros_medicos as rm', 'sr.registro_medico_id', '=', 'rm.id')
            ->where('sr.created_at', '>=', Carbon::now()->subDays(30))
            ->orderBy('sr.created_at', 'desc')
            ->limit(100)
            ->get();
    }

    private function getDashboardMetricsOptimized()
    {
        $today = Carbon::today();
        
        return [
            'today_count' => DB::table('solicitudes_referencia')
                ->whereDate('created_at', $today)
                ->count(),
            'pending_red' => DB::table('solicitudes_referencia')
                ->where('prioridad', 'ROJO')
                ->where('estado', 'PENDIENTE')
                ->count(),
            'auto_green' => DB::table('solicitudes_referencia')
                ->where('prioridad', 'VERDE')
                ->whereDate('created_at', $today)
                ->count()
        ];
    }

    private function getNotificationsOptimized()
    {
        return DB::table('notificaciones')
            ->select(['id', 'titulo', 'mensaje', 'prioridad', 'created_at', 'leida'])
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
    }

    public function cleanOldData()
    {
        $deleted = 0;
        
        // Limpiar notificaciones antiguas (>90 días)
        $deleted += DB::table('notificaciones')
            ->where('created_at', '<', Carbon::now()->subDays(90))
            ->delete();

        // Limpiar logs antiguos (>30 días)
        $deleted += DB::table('activity_log')
            ->where('created_at', '<', Carbon::now()->subDays(30))
            ->delete();

        Log::info("Database cleanup completed", ['deleted_records' => $deleted]);
        
        return $deleted;
    }

    public function analyzePerformance()
    {
        $slowQueries = DB::select("
            SELECT query_time, lock_time, rows_sent, rows_examined, sql_text
            FROM mysql.slow_log 
            WHERE start_time >= DATE_SUB(NOW(), INTERVAL 1 DAY)
            ORDER BY query_time DESC 
            LIMIT 10
        ");

        return [
            'slow_queries' => $slowQueries,
            'table_sizes' => $this->getTableSizes(),
            'index_usage' => $this->getIndexUsage()
        ];
    }

    private function getTableSizes()
    {
        return DB::select("
            SELECT 
                table_name,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                table_rows
            FROM information_schema.tables 
            WHERE table_schema = DATABASE()
            ORDER BY (data_length + index_length) DESC
        ");
    }

    private function getIndexUsage()
    {
        return DB::select("
            SELECT 
                table_name,
                index_name,
                cardinality,
                non_unique
            FROM information_schema.statistics 
            WHERE table_schema = DATABASE()
            ORDER BY cardinality DESC
        ");
    }
}