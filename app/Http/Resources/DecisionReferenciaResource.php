<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DecisionReferenciaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'decision' => $this->decision,
            'observaciones' => $this->observaciones,
            'decidido_por' => $this->decidido_por,
            'medico' => $this->whenLoaded('medico', function () {
                return $this->medico->name;
            }),
            'created_at' => $this->created_at,
        ];
    }
}