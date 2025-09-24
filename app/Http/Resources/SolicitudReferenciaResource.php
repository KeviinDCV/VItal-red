<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolicitudReferenciaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo_solicitud' => $this->codigo_solicitud,
            'prioridad' => $this->prioridad,
            'estado' => $this->estado,
            'fecha_solicitud' => $this->fecha_solicitud,
            'fecha_limite' => $this->fecha_limite,
            'puntuacion_ia' => $this->puntuacion_ia,
            'observaciones_ia' => $this->observaciones_ia,
            'registro_medico' => new RegistroMedicoResource($this->whenLoaded('registroMedico')),
            'decision' => new DecisionReferenciaResource($this->whenLoaded('decision')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}