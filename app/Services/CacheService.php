<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    public function getStats()
    {
        return [
            'total_keys' => $this->getTotalKeys(),
            'memory_usage' => $this->getMemoryUsage(),
            'hit_rate' => $this->getHitRate(),
            'active_connections' => $this->getActiveConnections()
        ];
    }

    public function getActiveKeys()
    {
        return Cache::getRedis()->keys('*');
    }

    public function clearMetricsCache()
    {
        Cache::tags(['metrics'])->flush();
    }

    public function clearAICache()
    {
        Cache::tags(['ai'])->flush();
    }

    public function clearSessionCache()
    {
        Cache::tags(['sessions'])->flush();
    }

    public function optimize()
    {
        $optimized = 0;
        $removed = 0;

        // Limpiar claves expiradas
        $keys = $this->getActiveKeys();
        foreach ($keys as $key) {
            if (Cache::get($key) === null) {
                Cache::forget($key);
                $removed++;
            } else {
                $optimized++;
            }
        }

        return [
            'optimized' => $optimized,
            'removed' => $removed
        ];
    }

    public function getDetailedMetrics()
    {
        return [
            'keys_count' => $this->getTotalKeys(),
            'memory_usage_mb' => $this->getMemoryUsage(),
            'hit_rate_percent' => $this->getHitRate(),
            'connections' => $this->getActiveConnections(),
            'uptime_seconds' => $this->getUptime()
        ];
    }

    private function getTotalKeys()
    {
        return count($this->getActiveKeys());
    }

    private function getMemoryUsage()
    {
        return Redis::info()['used_memory_human'] ?? '0MB';
    }

    private function getHitRate()
    {
        $info = Redis::info();
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        
        return $hits + $misses > 0 ? round(($hits / ($hits + $misses)) * 100, 2) : 0;
    }

    private function getActiveConnections()
    {
        return Redis::info()['connected_clients'] ?? 0;
    }

    private function getUptime()
    {
        return Redis::info()['uptime_in_seconds'] ?? 0;
    }
}