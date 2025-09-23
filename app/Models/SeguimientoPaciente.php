<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguimientoPaciente extends Model
{
    use HasFactory;

    protected $table = 'seguimiento_pacientes';

    protected $fillable = [
        'solicitud_referencia_id',
        'estado_seguimiento',
        'fecha_ingreso_real',
        'servicio_ingreso',
        'medico_tratante',
        'motivo_no_ingreso',
        'observaciones_no_ingreso',
        'evolucion_clinica',
        'fecha_alta_estimada',
        'fecha_alta_real',
        'tipo_alta',
        'requiere_contrarreferencia',
        'contrarreferencia_generada',
        'fecha_contrarreferencia',
        'actualizado_por',
    ];

    protected $casts = [
        'fecha_ingreso_real' => 'datetime',
        'fecha_alta_estimada' => 'date',
        'fecha_alta_real' => 'date',
        'fecha_contrarreferencia' => 'datetime',
        'requiere_contrarreferencia' => 'boolean',
    ];

    // Relaciones
    public function solicitudReferencia(): BelongsTo
    {
        return $this->belongsTo(SolicitudReferencia::class);
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }

    // Scopes
    public function scopeIngresados($query)
    {
        return $query->where('estado_seguimiento', 'INGRESADO');
    }

    public function scopeNoIngresados($query)
    {
        return $query->where('estado_seguimiento', 'NO_INGRESADO');
    }

    public function scopeEnProceso($query)
    {
        return $query->where('estado_seguimiento', 'EN_PROCESO');
    }

    public function scopeRequierenContrarreferencia($query)
    {
        return $query->where('requiere_contrarreferencia', true)
                    ->whereNull('contrarreferencia_generada');
    }

    // Métodos auxiliares
    public function ingreso(): bool
    {
        return $this->estado_seguimiento === 'INGRESADO';
    }

    public function noIngreso(): bool
    {
        return $this->estado_seguimiento === 'NO_INGRESADO';
    }

    public function tieneAlta(): bool
    {
        return !is_null($this->fecha_alta_real);
    }

    public function diasHospitalizacion(): ?int
    {
        if (!$this->fecha_ingreso_real || !$this->fecha_alta_real) {
            return null;
        }

        return $this->fecha_ingreso_real->diffInDays($this->fecha_alta_real);
    }
}