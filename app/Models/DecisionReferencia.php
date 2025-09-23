<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DecisionReferencia extends Model
{
    use HasFactory;

    protected $table = 'decisiones_referencia';

    protected $fillable = [
        'solicitud_referencia_id',
        'decidido_por',
        'decision',
        'justificacion',
        'especialista_asignado',
        'servicio_asignado',
        'fecha_cita_estimada',
        'instrucciones_paciente',
        'motivo_rechazo',
        'recomendaciones_alternativas',
        'fecha_decision',
        'datos_adicionales',
    ];

    protected $casts = [
        'fecha_decision' => 'datetime',
        'fecha_cita_estimada' => 'date',
        'datos_adicionales' => 'array',
    ];

    // Relaciones
    public function solicitudReferencia(): BelongsTo
    {
        return $this->belongsTo(SolicitudReferencia::class);
    }

    public function decididoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decidido_por');
    }

    // Scopes
    public function scopeAceptados($query)
    {
        return $query->where('decision', 'ACEPTADO');
    }

    public function scopeNoAdmitidos($query)
    {
        return $query->where('decision', 'NO_ADMITIDO');
    }

    public function scopePorEspecialista($query, $especialista)
    {
        return $query->where('especialista_asignado', $especialista);
    }

    // Métodos auxiliares
    public function fueAceptado(): bool
    {
        return $this->decision === 'ACEPTADO';
    }

    public function fueRechazado(): bool
    {
        return $this->decision === 'NO_ADMITIDO';
    }
}