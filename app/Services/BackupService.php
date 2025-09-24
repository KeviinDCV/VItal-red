<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BackupService
{
    public function createDatabaseBackup()
    {
        try {
            $filename = 'backup_' . Carbon::now()->format('Y_m_d_H_i_s') . '.sql';
            $path = storage_path('app/backups/' . $filename);
            
            // Crear directorio si no existe
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');

            $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > {$path}";
            
            exec($command, $output, $return);
            
            if ($return === 0) {
                Log::info('Backup creado exitosamente', ['file' => $filename]);
                return $filename;
            } else {
                throw new \Exception('Error ejecutando mysqldump');
            }
        } catch (\Exception $e) {
            Log::error('Error creando backup', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function cleanOldBackups($daysToKeep = 7)
    {
        $backupPath = storage_path('app/backups/');
        $cutoffDate = Carbon::now()->subDays($daysToKeep);
        
        $deleted = 0;
        if (is_dir($backupPath)) {
            $files = glob($backupPath . 'backup_*.sql');
            foreach ($files as $file) {
                if (filemtime($file) < $cutoffDate->timestamp) {
                    unlink($file);
                    $deleted++;
                }
            }
        }
        
        Log::info('Backups antiguos eliminados', ['deleted' => $deleted]);
        return $deleted;
    }

    public function getBackupsList()
    {
        $backupPath = storage_path('app/backups/');
        $backups = [];
        
        if (is_dir($backupPath)) {
            $files = glob($backupPath . 'backup_*.sql');
            foreach ($files as $file) {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => filesize($file),
                    'created_at' => Carbon::createFromTimestamp(filemtime($file))
                ];
            }
        }
        
        return collect($backups)->sortByDesc('created_at')->values();
    }
}