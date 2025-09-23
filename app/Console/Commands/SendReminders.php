<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SolicitudReferencia;
use App\Models\User;
use App\Services\EmailService;
use App\Services\SMSService;
use Carbon\Carbon;

class SendReminders extends Command
{
    protected $signature = 'reminders:send {type=pending}';
    protected $description = 'Enviar recordatorios automáticos';

    public function handle()
    {
        $type = $this->argument('type');
        
        $this->info("Enviando recordatorios: {$type}");
        
        switch ($type) {
            case 'pending':
                return $this->sendPendingReminders();
            case 'critical':
                return $this->sendCriticalReminders();
            default:
                $this->error('Tipo no válido. Use: pending, critical');
                return 1;
        }
    }
    
    private function sendPendingReminders()
    {
        $emailService = new EmailService();
        $sent = 0;
        
        // Solicitudes pendientes > 1 hora
        $pendingSolicitudes = SolicitudReferencia::where('estado', 'PENDIENTE')
            ->where('created_at', '<', Carbon::now()->subHour())
            ->get();
            
        foreach ($pendingSolicitudes as $solicitud) {
            $message = "Solicitud {$solicitud->codigo_solicitud} pendiente de revisión desde hace más de 1 hora.";
            
            // Enviar a médicos disponibles
            $medicos = User::where('role', 'medico')->get();
            foreach ($medicos as $medico) {
                if ($emailService->sendCriticalAlert($medico->email, 'Solicitud Pendiente', $message)) {
                    $sent++;
                }
            }
        }
        
        $this->info("Recordatorios enviados: {$sent}");
        return 0;
    }
    
    private function sendCriticalReminders()
    {
        $emailService = new EmailService();
        $smsService = new SMSService();
        $sent = 0;
        
        // Casos críticos > 2 horas
        $criticalCases = SolicitudReferencia::where('prioridad', 'ROJO')
            ->where('estado', 'PENDIENTE')
            ->where('created_at', '<', Carbon::now()->subHours(2))
            ->get();
            
        foreach ($criticalCases as $caso) {
            $message = "CRÍTICO: Caso ROJO {$caso->codigo_solicitud} sin atender por más de 2 horas.";
            
            // Enviar a jefes de urgencias
            $jefes = User::where('role', 'jefe-urgencias')->get();
            foreach ($jefes as $jefe) {
                $emailService->sendCriticalAlert($jefe->email, 'ALERTA CRÍTICA', $message);
                if ($jefe->telefono) {
                    $smsService->sendUrgentSMS($jefe->telefono, $message);
                }
                $sent++;
            }
        }
        
        $this->info("Alertas críticas enviadas: {$sent}");
        return 0;
    }
}