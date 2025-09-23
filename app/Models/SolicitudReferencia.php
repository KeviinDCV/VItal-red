<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudReferencia extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_referencia';

    protected $fillable = [
        'registro_medico_id',
        'codigo_solicitud',
        'prioridad',
        'estado',
        'fecha_solicitud',
        'fecha_limite',
        'fecha_decision',
        'puntuacion_ia',
        'factores_priorizacion',
        'observaciones_ia',
        'observaciones_medico',
        'procesado_por',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_limite' => 'datetime',
        'fecha_decision' => 'datetime',
        'factores_priorizacion' => 'array',
        'puntuacion_ia' => 'decimal:2',
    ];

    // Relaciones
    public function registroMedico(): BelongsTo
    {
        return $this->belongsTo(RegistroMedico::class);
    }

    public function procesadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'procesado_por');
    }

    public function decision()
    {
        return $this->hasOne(DecisionReferencia::class);
    }

    public function seguimiento()
    {
        return $this->hasOne(SeguimientoPaciente::class);
    }

    // Scopes para consultas comunes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'PENDIENTE');
    }

    public function scopePrioridadRoja($query)
    {
        return $query->where('prioridad', 'ROJO');
    }

    public function scopePrioridadVerde($query)
    {
        return $query->where('prioridad', 'VERDE');
    }

    public function scopeProximasAVencer($query, $horas = 2)
    {
        return $query->where('fecha_limite', '<=', now()->addHours($horas))
                    ->where('estado', 'PENDIENTE');
    }

    // Métodos auxiliares
    public function esPrioridadRoja(): bool
    {
        return $this->prioridad === 'ROJO';
    }

    public function estaPendiente(): bool
    {
        return $this->estado === 'PENDIENTE';
    }

    public function tiempoTranscurrido(): string
    {
        $diff = now()->diff($this->fecha_solicitud);
        
        if ($diff->days > 0) {
            return $diff->days . ' día(s)';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hora(s)';
        } else {
            return $diff->i . ' minuto(s)';
        }
    }

    public function tiempoRestante(): ?string
    {
        if (!$this->fecha_limite || $this->estado !== 'PENDIENTE') {
            return null;
        }

        $diff = $this->fecha_limite->diff(now());
        
        if ($this->fecha_limite < now()) {
            return 'VENCIDO';
        }

        if ($diff->days > 0) {
            return $diff->days . ' día(s)';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hora(s)';
        } else {
            return $diff->i . ' minuto(s)';
        }
    }

    // Generar código único de solicitud
    public static function generarCodigoSolicitud(): string
    {
        do {
            $codigo = 'REF-' . date('Y') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('codigo_solicitud', $codigo)->exists());

        return $codigo;
    }
}