<?php

namespace App\Jobs;

use App\Models\SolicitudReferencia;
use App\Services\AutoResponseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendAutomaticResponseJob implements ShouldQueue
{
    use Queueable;

    protected $solicitudId;

    public function __construct($solicitudId)
    {
        $this->solicitudId = $solicitudId;
    }

    public function handle(AutoResponseService $autoResponseService): void
    {
        try {
            $solicitud = SolicitudReferencia::find($this->solicitudId);
            
            if (!$solicitud) {
                Log::warning("Solicitud {$this->solicitudId} no encontrada para respuesta automática");
                return;
            }

            // Solo procesar casos VERDES
            if ($solicitud->prioridad_ia === 'VERDE') {
                $success = $autoResponseService->processGreenCase($solicitud);
                
                if ($success) {
                    Log::info("Respuesta automática enviada para solicitud {$solicitud->codigo_solicitud}");
                } else {
                    Log::error("Error enviando respuesta automática para solicitud {$solicitud->codigo_solicitud}");
                }
            }
            
        } catch (\Exception $e) {
            Log::error("Error en SendAutomaticResponseJob {$this->solicitudId}: " . $e->getMessage());
            throw $e;
        }
    }
}
