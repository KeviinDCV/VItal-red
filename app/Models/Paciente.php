<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellidos',
        'documento',
        'tipo_documento',
        'fecha_nacimiento',
        'telefono',
        'email',
        'direccion',
        'ciudad',
        'eps',
        'regimen',
        'estado_civil',
        'ocupacion',
        'contacto_emergencia',
        'telefono_emergencia'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date'
    ];

    public function registrosMedicos()
    {
        return $this->hasMany(RegistroMedico::class);
    }

    public function getEdadAttribute()
    {
        return $this->fecha_nacimiento ? $this->fecha_nacimiento->age : null;
    }

    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellidos}";
    }

    public function scopeByDocumento($query, $documento)
    {
        return $query->where('documento', $documento);
    }
}