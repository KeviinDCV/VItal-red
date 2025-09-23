<?php

namespace App\Jobs;

use App\Models\SystemMetrics;
use App\Models\AIClassificationLog;
use App\Models\Notificacion;
use App\Models\CriticalAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupOldDataJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $deletedCount = 0;
        
        try {
            // Limpiar métricas antiguas (> 30 días)
            $deleted = SystemMetrics::where('created_at', '<', Carbon::now()->subDays(30))->delete();
            $deletedCount += $deleted;
            Log::info("Deleted {$deleted} old system metrics");
            
            // Limpiar logs de IA antiguos (> 90 días)
            $deleted = AIClassificationLog::where('created_at', '<', Carbon::now()->subDays(90))->delete();
            $deletedCount += $deleted;
            Log::info("Deleted {$deleted} old AI classification logs");
            
            // Limpiar notificaciones leídas antiguas (> 30 días)
            $deleted = Notificacion::where('leida', true)
                ->where('leida_at', '<', Carbon::now()->subDays(30))
                ->delete();
            $deletedCount += $deleted;
            Log::info("Deleted {$deleted} old read notifications");
            
            // Limpiar alertas resueltas antiguas (> 60 días)
            $deleted = CriticalAlert::where('status', 'resolved')
                ->where('resolved_at', '<', Carbon::now()->subDays(60))
                ->delete();
            $deletedCount += $deleted;
            Log::info("Deleted {$deleted} old resolved alerts");
            
            // Limpiar sesiones expiradas
            $deleted = \DB::table('sessions')
                ->where('last_activity', '<', Carbon::now()->subDays(7)->timestamp)
                ->delete();
            $deletedCount += $deleted;
            Log::info("Deleted {$deleted} expired sessions");
            
            Log::info("CleanupOldDataJob completed. Total deleted records: {$deletedCount}");
            
        } catch (\Exception $e) {
            Log::error("Error in CleanupOldDataJob: " . $e->getMessage());
            throw $e;
        }
    }
}
