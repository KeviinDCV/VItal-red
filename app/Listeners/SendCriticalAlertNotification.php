<?php

namespace App\Listeners;

use App\Events\CriticalAlertCreated;
use App\Services\EmailService;
use App\Services\SMSService;
use App\Models\User;

class SendCriticalAlertNotification
{
    public function __construct(
        private EmailService $emailService,
        private SMSService $smsService
    ) {}

    public function handle(CriticalAlertCreated $event): void
    {
        $alert = $event->alert;
        
        // Enviar a jefes de urgencias
        $jefes = User::where('role', 'jefe-urgencias')->get();
        
        foreach ($jefes as $jefe) {
            $this->emailService->sendCriticalAlert(
                $jefe->email,
                'ALERTA CRÍTICA: ' . $alert->titulo,
                $alert->mensaje
            );
            
            if ($jefe->telefono) {
                $this->smsService->sendUrgentSMS(
                    $jefe->telefono,
                    'CRÍTICO: ' . $alert->titulo
                );
            }
        }
    }
}