<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recurso extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo',
        'nombre',
        'especialidad',
        'ubicacion',
        'estado',
        'capacidad_maxima',
        'capacidad_actual',
        'turno',
        'contacto',
        'metricas'
    ];

    protected $casts = [
        'contacto' => 'array',
        'metricas' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'DISPONIBLE');
    }

    public function scopeOcupados($query)
    {
        return $query->where('estado', 'OCUPADO');
    }

    public function scopeMantenimiento($query)
    {
        return $query->where('estado', 'MANTENIMIENTO');
    }

    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function calcularUtilizacion()
    {
        if ($this->capacidad_maxima && $this->capacidad_actual !== null) {
            return round(($this->capacidad_actual / $this->capacidad_maxima) * 100);
        }
        return 0;
    }
}