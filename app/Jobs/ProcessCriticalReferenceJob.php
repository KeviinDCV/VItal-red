<?php

namespace App\Jobs;

use App\Models\SolicitudReferencia;
use App\Services\CriticalAlertService;
use App\Services\RealTimeNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessCriticalReferenceJob implements ShouldQueue
{
    use Queueable;

    protected $solicitudId;

    public function __construct($solicitudId)
    {
        $this->solicitudId = $solicitudId;
    }

    public function handle(CriticalAlertService $alertService, RealTimeNotificationService $notificationService): void
    {
        try {
            $solicitud = SolicitudReferencia::find($this->solicitudId);
            
            if (!$solicitud) {
                Log::warning("Solicitud {$this->solicitudId} no encontrada");
                return;
            }

            // Si es caso ROJO, crear alerta inmediata
            if ($solicitud->prioridad_ia === 'ROJO') {
                $alertService->createTimeoutAlert($solicitud);
                
                // Notificar a médicos disponibles
                $medicos = \App\Models\User::where('role', 'medico')->get();
                foreach ($medicos as $medico) {
                    $notificationService->sendNotification(
                        $medico->id,
                        "🚨 CASO CRÍTICO: {$solicitud->codigo_solicitud}",
                        "Paciente {$solicitud->registroMedico->nombre} requiere atención inmediata - {$solicitud->especialidad_solicitada}",
                        'critical',
                        ['solicitud_id' => $solicitud->id]
                    );
                }
            }
            
            Log::info("Procesada referencia crítica: {$solicitud->codigo_solicitud}");
            
        } catch (\Exception $e) {
            Log::error("Error procesando referencia crítica {$this->solicitudId}: " . $e->getMessage());
            throw $e;
        }
    }
}
