<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionIA extends Model
{
    use HasFactory;

    protected $table = 'configuracion_ia';

    protected $fillable = [
        'nombre',
        'descripcion',
        'parametros',
        'activo',
        'version',
        'actualizado_por'
    ];

    protected $casts = [
        'parametros' => 'array',
        'activo' => 'boolean'
    ];

    // Relaciones
    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Métodos auxiliares
    public static function obtenerConfiguracion($nombre)
    {
        return self::where('nombre', $nombre)->where('activo', true)->first();
    }

    public static function configuracionPrioridad()
    {
        $config = self::obtenerConfiguracion('algoritmo_prioridad');
        
        return $config ? $config->parametros : [
            'peso_edad' => 0.3,
            'peso_gravedad' => 0.4,
            'peso_tiempo_espera' => 0.2,
            'peso_especialidad' => 0.1,
            'umbral_rojo' => 0.7
        ];
    }

    public function actualizarParametros(array $nuevosParametros, $userId)
    {
        $this->update([
            'parametros' => $nuevosParametros,
            'actualizado_por' => $userId,
            'version' => $this->incrementarVersion()
        ]);
    }

    private function incrementarVersion()
    {
        $version = explode('.', $this->version);
        $version[1] = (int)$version[1] + 1;
        return implode('.', $version);
    }
}