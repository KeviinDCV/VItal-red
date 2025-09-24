<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
    public function check()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
            'ai_service' => $this->checkAIService()
        ];

        $allHealthy = collect($checks)->every(fn($check) => $check['status'] === 'ok');
        $status = $allHealthy ? 200 : 503;

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
            'version' => config('app.version', '1.0.0')
        ], $status);
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('SELECT 1');
            return ['status' => 'ok', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    private function checkRedis(): array
    {
        try {
            Redis::ping();
            return ['status' => 'ok', 'message' => 'Redis connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Redis connection failed: ' . $e->getMessage()];
        }
    }

    private function checkStorage(): array
    {
        try {
            $testFile = storage_path('app/health-check.txt');
            file_put_contents($testFile, 'health check');
            $content = file_get_contents($testFile);
            unlink($testFile);
            
            return $content === 'health check' 
                ? ['status' => 'ok', 'message' => 'Storage is writable']
                : ['status' => 'error', 'message' => 'Storage write/read failed'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Storage check failed: ' . $e->getMessage()];
        }
    }

    private function checkQueue(): array
    {
        try {
            $queueSize = Redis::llen('queues:default');
            return ['status' => 'ok', 'message' => "Queue operational, {$queueSize} jobs pending"];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Queue check failed: ' . $e->getMessage()];
        }
    }

    private function checkAIService(): array
    {
        try {
            // Simple AI service health check
            $testData = ['test' => 'health check'];
            $result = app(\App\Services\GeminiAIService::class)->testConnection();
            
            return $result 
                ? ['status' => 'ok', 'message' => 'AI service operational']
                : ['status' => 'warning', 'message' => 'AI service not responding'];
        } catch (\Exception $e) {
            return ['status' => 'warning', 'message' => 'AI service check failed: ' . $e->getMessage()];
        }
    }

    public function ready()
    {
        // Readiness probe - checks if app is ready to serve traffic
        $ready = $this->checkDatabase()['status'] === 'ok' && 
                 $this->checkRedis()['status'] === 'ok';

        return response()->json([
            'ready' => $ready,
            'timestamp' => now()->toISOString()
        ], $ready ? 200 : 503);
    }

    public function live()
    {
        // Liveness probe - basic check if app is running
        return response()->json([
            'alive' => true,
            'timestamp' => now()->toISOString(),
            'uptime' => $this->getUptime()
        ]);
    }

    private function getUptime(): string
    {
        $uptime = file_get_contents('/proc/uptime');
        $seconds = (int) explode(' ', $uptime)[0];
        
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        return "{$days}d {$hours}h {$minutes}m";
    }
}