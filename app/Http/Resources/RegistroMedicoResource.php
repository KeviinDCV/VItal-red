<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistroMedicoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            'edad' => $this->edad,
            'sexo' => $this->sexo,
            'diagnostico_principal' => $this->diagnostico_principal,
            'especialidad_solicitada' => $this->especialidad_solicitada,
            'motivo_remision' => $this->motivo_remision,
            'created_at' => $this->created_at,
        ];
    }
}