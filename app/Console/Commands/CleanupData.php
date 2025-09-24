<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EventoAuditoria;
use App\Models\Notificacion;
use App\Services\BackupService;
use Carbon\Carbon;

class CleanupData extends Command
{
    protected $signature = 'data:cleanup {--days=30} {--backup}';
    protected $description = 'Limpiar datos antiguos del sistema';

    public function handle()
    {
        $days = $this->option('days');
        $backup = $this->option('backup');
        
        $this->info("Iniciando limpieza de datos antiguos ({$days} días)...");
        
        if ($backup) {
            $this->info('Creando backup antes de limpiar...');
            $backupService = new BackupService();
            $backupFile = $backupService->createDatabaseBackup();
            
            if ($backupFile) {
                $this->info("Backup creado: {$backupFile}");
            } else {
                $this->error('Error creando backup. Cancelando limpieza.');
                return 1;
            }
        }
        
        $cutoffDate = Carbon::now()->subDays($days);
        
        // Limpiar eventos de auditoría
        $auditDeleted = EventoAuditoria::where('created_at', '<', $cutoffDate)->delete();
        $this->info("Eventos de auditoría eliminados: {$auditDeleted}");
        
        // Limpiar notificaciones leídas
        $notifDeleted = Notificacion::where('created_at', '<', $cutoffDate)
            ->where('leida', true)
            ->delete();
        $this->info("Notificaciones eliminadas: {$notifDeleted}");
        
        // Limpiar backups antiguos
        if ($backup) {
            $backupService = new BackupService();
            $backupsDeleted = $backupService->cleanOldBackups(7);
            $this->info("Backups antiguos eliminados: {$backupsDeleted}");
        }
        
        $this->info('Limpieza completada exitosamente');
        return 0;
    }
}