<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Events\NuevaNotificacion;
use Carbon\Carbon;

class MonitoringService
{
    private $thresholds = [
        'response_time' => 200, // ms
        'memory_usage' => 80,   // %
        'cpu_usage' => 85,      // %
        'disk_usage' => 90,     // %
        'error_rate' => 5       // %
    ];

    public function checkSystemHealth()
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => now(),
            'metrics' => [
                'database' => $this->checkDatabaseHealth(),
                'cache' => $this->checkCacheHealth(),
                'memory' => $this->checkMemoryUsage(),
                'response_time' => $this->checkResponseTime(),
                'error_rate' => $this->checkErrorRate()
            ],
            'alerts' => []
        ];

        // Check thresholds and generate alerts
        foreach ($health['metrics'] as $metric => $value) {
            if (isset($this->thresholds[$metric]) && $value > $this->thresholds[$metric]) {
                $health['status'] = 'warning';
                $health['alerts'][] = [
                    'metric' => $metric,
                    'value' => $value,
                    'threshold' => $this->thresholds[$metric],
                    'severity' => $this->getSeverity($metric, $value)
                ];
            }
        }

        // Log health status
        Log::channel('monitoring')->info('System Health Check', $health);

        // Send alerts if needed
        if (!empty($health['alerts'])) {
            $this->sendHealthAlerts($health['alerts']);
        }

        return $health;
    }

    public function logPerformanceMetric($operation, $duration, $metadata = [])
    {
        $metric = [
            'operation' => $operation,
            'duration_ms' => $duration,
            'timestamp' => now(),
            'metadata' => $metadata
        ];

        Log::channel('performance')->info('Performance Metric', $metric);

        // Check if operation is slow
        if ($duration > $this->thresholds['response_time']) {
            $this->alertSlowOperation($operation, $duration);
        }
    }

    public function getSystemMetrics()
    {
        return Cache::remember('system_metrics', 60, function () {
            return [
                'uptime' => $this->getUptime(),
                'memory_usage' => $this->getMemoryUsage(),
                'cpu_usage' => $this->getCPUUsage(),
                'disk_usage' => $this->getDiskUsage(),
                'active_users' => $this->getActiveUsers(),
                'requests_per_minute' => $this->getRequestsPerMinute(),
                'error_rate' => $this->getErrorRate(),
                'database_connections' => $this->getDatabaseConnections()
            ];
        });
    }

    private function checkDatabaseHealth()
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $duration = (microtime(true) - $start) * 1000;
            
            return $duration;
        } catch (\Exception $e) {
            Log::error('Database health check failed', ['error' => $e->getMessage()]);
            return 9999; // High value to trigger alert
        }
    }

    private function checkCacheHealth()
    {
        try {
            $start = microtime(true);
            Cache::put('health_check', 'ok', 10);
            Cache::get('health_check');
            $duration = (microtime(true) - $start) * 1000;
            
            return $duration;
        } catch (\Exception $e) {
            Log::error('Cache health check failed', ['error' => $e->getMessage()]);
            return 9999;
        }
    }

    private function checkMemoryUsage()
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseBytes(ini_get('memory_limit'));
        
        return $memoryLimit > 0 ? ($memoryUsage / $memoryLimit) * 100 : 0;
    }

    private function checkResponseTime()
    {
        // Get average response time from recent logs
        return Cache::remember('avg_response_time', 300, function () {
            // This would typically come from APM or log analysis
            return rand(50, 150); // Simulated for demo
        });
    }

    private function checkErrorRate()
    {
        // Calculate error rate from logs
        return Cache::remember('error_rate', 300, function () {
            // This would analyze error logs
            return rand(0, 3); // Simulated for demo
        });
    }

    private function getSeverity($metric, $value)
    {
        $threshold = $this->thresholds[$metric];
        $ratio = $value / $threshold;
        
        if ($ratio >= 2) return 'critical';
        if ($ratio >= 1.5) return 'high';
        if ($ratio >= 1.2) return 'medium';
        return 'low';
    }

    private function sendHealthAlerts($alerts)
    {
        foreach ($alerts as $alert) {
            if ($alert['severity'] === 'critical' || $alert['severity'] === 'high') {
                event(new NuevaNotificacion([
                    'tipo' => 'system_alert',
                    'titulo' => 'Alerta de Sistema',
                    'mensaje' => "Métrica {$alert['metric']} excede umbral: {$alert['value']} > {$alert['threshold']}",
                    'prioridad' => 'ALTA',
                    'usuario_id' => null, // Send to all admins
                    'metadata' => $alert
                ]));
            }
        }
    }

    private function alertSlowOperation($operation, $duration)
    {
        Log::warning('Slow Operation Detected', [
            'operation' => $operation,
            'duration_ms' => $duration,
            'threshold_ms' => $this->thresholds['response_time']
        ]);
    }

    private function parseBytes($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        
        return round($size);
    }

    private function getUptime()
    {
        // System uptime calculation
        return '99.9%'; // Simulated
    }

    private function getMemoryUsage()
    {
        return round((memory_get_usage(true) / 1024 / 1024), 2); // MB
    }

    private function getCPUUsage()
    {
        // CPU usage calculation (would use system commands in production)
        return rand(10, 30); // Simulated
    }

    private function getDiskUsage()
    {
        $bytes = disk_free_space('/');
        $total = disk_total_space('/');
        
        return $total > 0 ? round((($total - $bytes) / $total) * 100, 2) : 0;
    }

    private function getActiveUsers()
    {
        return Cache::remember('active_users', 300, function () {
            // Count active sessions or recent activity
            return rand(50, 200); // Simulated
        });
    }

    private function getRequestsPerMinute()
    {
        return Cache::remember('requests_per_minute', 60, function () {
            // Calculate from access logs
            return rand(100, 500); // Simulated
        });
    }

    private function getErrorRate()
    {
        return Cache::remember('current_error_rate', 300, function () {
            // Calculate from error logs
            return rand(0, 2); // Simulated
        });
    }

    private function getDatabaseConnections()
    {
        try {
            $result = DB::select('SHOW STATUS LIKE "Threads_connected"');
            return $result[0]->Value ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}