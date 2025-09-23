<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Models\SolicitudReferencia;
use App\Models\User;

class CacheService
{
    private $defaultTTL = 3600; // 1 hour
    private $shortTTL = 300;    // 5 minutes
    private $longTTL = 86400;   // 24 hours

    public function getDashboardMetrics($userId)
    {
        $key = "dashboard_metrics_{$userId}";
        
        return Cache::remember($key, $this->shortTTL, function () use ($userId) {
            $user = User::find($userId);
            
            switch ($user->role) {
                case 'administrador':
                    return $this->getAdminMetrics();
                case 'medico':
                    return $this->getMedicoMetrics();
                case 'ips':
                    return $this->getIPSMetrics($userId);
                default:
                    return [];
            }
        });
    }

    public function getSolicitudesPendientes()
    {
        return Cache::remember('solicitudes_pendientes', $this->shortTTL, function () {
            return SolicitudReferencia::with(['registroMedico'])
                ->where('estado', 'PENDIENTE')
                ->where('prioridad', 'ROJO')
                ->orderBy('created_at', 'asc')
                ->limit(20)
                ->get();
        });
    }

    public function getIAClassificationCache($textHash)
    {
        $key = "ia_classification_{$textHash}";
        return Cache::get($key);
    }

    public function setIAClassificationCache($textHash, $result)
    {
        $key = "ia_classification_{$textHash}";
        Cache::put($key, $result, $this->longTTL);
    }

    public function getUserSession($userId)
    {
        return Cache::remember("user_session_{$userId}", $this->defaultTTL, function () use ($userId) {
            return User::with(['permissions'])->find($userId);
        });
    }

    public function invalidateUserCache($userId)
    {
        Cache::forget("user_session_{$userId}");
        Cache::forget("dashboard_metrics_{$userId}");
    }

    public function invalidateDashboardCache()
    {
        $pattern = 'dashboard_metrics_*';
        $keys = Redis::keys($pattern);
        
        if (!empty($keys)) {
            Redis::del($keys);
        }
        
        Cache::forget('solicitudes_pendientes');
    }

    public function getSpecialtyStats()
    {
        return Cache::remember('specialty_stats', $this->defaultTTL, function () {
            return SolicitudReferencia::join('registros_medicos', 'solicitudes_referencia.registro_medico_id', '=', 'registros_medicos.id')
                ->selectRaw('especialidad_solicitada, COUNT(*) as total, AVG(puntuacion_ia) as avg_score')
                ->groupBy('especialidad_solicitada')
                ->orderBy('total', 'desc')
                ->get();
        });
    }

    public function getCacheStats()
    {
        $redis = Redis::connection();
        
        return [
            'memory_usage' => $redis->info('memory')['used_memory_human'] ?? 'N/A',
            'connected_clients' => $redis->info('clients')['connected_clients'] ?? 0,
            'total_commands' => $redis->info('stats')['total_commands_processed'] ?? 0,
            'hit_rate' => $this->calculateHitRate(),
            'keys_count' => $redis->dbsize()
        ];
    }

    private function calculateHitRate()
    {
        $redis = Redis::connection();
        $stats = $redis->info('stats');
        
        $hits = $stats['keyspace_hits'] ?? 0;
        $misses = $stats['keyspace_misses'] ?? 0;
        $total = $hits + $misses;
        
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    private function getAdminMetrics()
    {
        return [
            'total_solicitudes' => SolicitudReferencia::count(),
            'pendientes' => SolicitudReferencia::where('estado', 'PENDIENTE')->count(),
            'completados' => SolicitudReferencia::where('estado', '!=', 'PENDIENTE')->count()
        ];
    }

    private function getMedicoMetrics()
    {
        return [
            'casos_criticos' => SolicitudReferencia::where('prioridad', 'ROJO')->where('estado', 'PENDIENTE')->count(),
            'procesados_hoy' => SolicitudReferencia::whereDate('created_at', today())->count()
        ];
    }

    private function getIPSMetrics($userId)
    {
        return [
            'mis_solicitudes' => SolicitudReferencia::where('usuario_id', $userId)->count(),
            'pendientes' => SolicitudReferencia::where('usuario_id', $userId)->where('estado', 'PENDIENTE')->count()
        ];
    }
}