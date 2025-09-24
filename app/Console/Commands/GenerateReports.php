<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReportService;
use App\Services\EmailService;
use Carbon\Carbon;

class GenerateReports extends Command
{
    protected $signature = 'reports:generate {type=daily} {--email=}';
    protected $description = 'Generar reportes del sistema';

    public function handle()
    {
        $type = $this->argument('type');
        $email = $this->option('email');
        
        $reportService = new ReportService();
        
        $this->info("Generando reporte {$type}...");
        
        switch ($type) {
            case 'daily':
                $data = $reportService->generateDailyReport();
                break;
            case 'weekly':
                $data = $reportService->generateWeeklyReport();
                break;
            default:
                $this->error('Tipo de reporte no válido. Use: daily, weekly');
                return 1;
        }
        
        $this->table(array_keys($data), [array_values($data)]);
        
        if ($email) {
            $emailService = new EmailService();
            $message = "Reporte {$type} generado:\n" . json_encode($data, JSON_PRETTY_PRINT);
            
            if ($emailService->sendCriticalAlert($email, "Reporte {$type}", $message)) {
                $this->info("Reporte enviado a {$email}");
            } else {
                $this->error("Error enviando reporte");
            }
        }
        
        return 0;
    }
}