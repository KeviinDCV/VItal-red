<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialPaciente extends Model
{
    use HasFactory;

    protected $table = 'historial_pacientes';

    protected $fillable = [
        'paciente_id',
        'consultas',
        'referencias',
        'ultima_consulta',
        'total_consultas',
        'total_referencias'
    ];

    protected $casts = [
        'consultas' => 'array',
        'referencias' => 'array',
        'ultima_consulta' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function consultasActivas()
    {
        $consultas = $this->consultas ?? [];
        return collect($consultas)->where('estado', 'ACTIVO')->count();
    }

    public function referenciasPendientes()
    {
        $referencias = $this->referencias ?? [];
        return collect($referencias)->where('estado', 'PENDIENTE')->count();
    }

    public function scopeConConsultasRecientes($query, $dias = 30)
    {
        return $query->where('ultima_consulta', '>=', now()->subDays($dias));
    }
}