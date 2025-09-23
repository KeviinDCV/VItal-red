<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IPS extends Model
{
    use HasFactory;

    protected $table = 'ips';

    protected $fillable = [
        'codigo_prestador',
        'nombre',
        'nit',
        'tipo_ips',
        'departamento',
        'municipio',
        'direccion',
        'telefono',
        'email',
        'tiene_hospitalizacion',
        'tiene_urgencias',
        'tiene_uci',
        'tiene_cirugia',
        'especialidades_disponibles',
        'contacto_referencias',
        'telefono_referencias',
        'email_referencias',
        'activa',
        'acepta_referencias',
        'capacidad_diaria',
        'configuracion_adicional',
        'fecha_registro',
    ];

    protected $casts = [
        'tiene_hospitalizacion' => 'boolean',
        'tiene_urgencias' => 'boolean',
        'tiene_uci' => 'boolean',
        'tiene_cirugia' => 'boolean',
        'activa' => 'boolean',
        'acepta_referencias' => 'boolean',
        'especialidades_disponibles' => 'array',
        'configuracion_adicional' => 'array',
        'fecha_registro' => 'datetime',
    ];

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopeAceptanReferencias($query)
    {
        return $query->where('acepta_referencias', true);
    }

    public function scopeConHospitalizacion($query)
    {
        return $query->where('tiene_hospitalizacion', true);
    }

    public function scopeConUrgencias($query)
    {
        return $query->where('tiene_urgencias', true);
    }

    public function scopePorDepartamento($query, $departamento)
    {
        return $query->where('departamento', $departamento);
    }

    public function scopePorMunicipio($query, $municipio)
    {
        return $query->where('municipio', $municipio);
    }

    // Métodos auxiliares
    public function puedeRecibirReferencias(): bool
    {
        return $this->activa && $this->acepta_referencias;
    }

    public function tieneEspecialidad(string $especialidad): bool
    {
        return in_array($especialidad, $this->especialidades_disponibles ?? []);
    }

    public function getServiciosDisponiblesAttribute(): array
    {
        $servicios = [];
        
        if ($this->tiene_hospitalizacion) $servicios[] = 'Hospitalización';
        if ($this->tiene_urgencias) $servicios[] = 'Urgencias';
        if ($this->tiene_uci) $servicios[] = 'UCI';
        if ($this->tiene_cirugia) $servicios[] = 'Cirugía';
        
        return $servicios;
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} - {$this->municipio}, {$this->departamento}";
    }
}