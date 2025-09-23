<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventoAuditoria extends Model
{
    use HasFactory;

    protected $table = 'eventos_auditoria';

    protected $fillable = [
        'user_id',
        'accion',
        'recurso',
        'ip_address',
        'user_agent',
        'nivel_riesgo',
        'detalles',
        'ubicacion'
    ];

    protected $casts = [
        'detalles' => 'array',
        'ubicacion' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeNivelRiesgo($query, $nivel)
    {
        return $query->where('nivel_riesgo', $nivel);
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeIntentosFallidos($query)
    {
        return $query->where('accion', 'LIKE', '%login%')
                    ->where('detalles->respuesta_codigo', '>=', 400);
    }
}