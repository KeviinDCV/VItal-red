<?php

namespace App\Observers;

use App\Models\SolicitudReferencia;
use App\Models\Notificacion;
use App\Events\CriticalAlertCreated;

class SolicitudReferenciaObserver
{
    public function created(SolicitudReferencia $solicitud): void
    {
        // Crear notificación para médicos
        $medicos = \App\Models\User::where('role', 'medico')->get();
        
        foreach ($medicos as $medico) {
            Notificacion::create([
                'user_id' => $medico->id,
                'tipo' => 'solicitud_nueva',
                'titulo' => 'Nueva Solicitud de Referencia',
                'mensaje' => "Nueva solicitud {$solicitud->codigo_solicitud} - Prioridad: {$solicitud->prioridad}",
                'datos' => json_encode(['solicitud_id' => $solicitud->id]),
                'prioridad' => $solicitud->prioridad === 'ROJO' ? 'alta' : 'normal'
            ]);
        }

        // Si es crítica, disparar alerta
        if ($solicitud->prioridad === 'ROJO') {
            event(new CriticalAlertCreated((object)[
                'titulo' => 'Caso Crítico Ingresado',
                'mensaje' => "Solicitud {$solicitud->codigo_solicitud} requiere atención inmediata",
                'solicitud_id' => $solicitud->id
            ]));
        }
    }

    public function updated(SolicitudReferencia $solicitud): void
    {
        if ($solicitud->isDirty('estado') && $solicitud->estado !== 'PENDIENTE') {
            // Notificar a la IPS que creó la solicitud
            if ($solicitud->registroMedico && $solicitud->registroMedico->user) {
                Notificacion::create([
                    'user_id' => $solicitud->registroMedico->user_id,
                    'tipo' => 'decision_tomada',
                    'titulo' => 'Solicitud Evaluada',
                    'mensaje' => "Su solicitud {$solicitud->codigo_solicitud} ha sido {$solicitud->estado}",
                    'datos' => json_encode(['solicitud_id' => $solicitud->id])
                ]);
            }
        }
    }
}