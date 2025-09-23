<?php

namespace App\Listeners;

use App\Models\EventoAuditoria;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuditEvent
{
    public function handle($event): void
    {
        $eventType = class_basename($event);
        $userId = auth()->id();
        
        EventoAuditoria::create([
            'user_id' => $userId,
            'evento' => $eventType,
            'descripcion' => $this->getEventDescription($eventType),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'datos_adicionales' => json_encode([
                'timestamp' => now(),
                'session_id' => session()->getId()
            ])
        ]);
    }
    
    private function getEventDescription(string $eventType): string
    {
        return match($eventType) {
            'Login' => 'Usuario inició sesión',
            'Logout' => 'Usuario cerró sesión',
            default => 'Evento del sistema: ' . $eventType
        };
    }
}