<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id',
        'tipo',
        'titulo',
        'mensaje',
        'datos',
        'leida',
        'leida_en',
        'prioridad'
    ];

    protected $casts = [
        'datos' => 'array',
        'leida' => 'boolean',
        'leida_en' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    // Métodos auxiliares
    public function marcarComoLeida()
    {
        $this->update([
            'leida' => true,
            'leida_en' => now()
        ]);
    }

    public static function crearNotificacion($userId, $tipo, $titulo, $mensaje, $datos = null, $prioridad = 'media')
    {
        $notificacion = self::create([
            'user_id' => $userId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'datos' => $datos,
            'prioridad' => $prioridad
        ]);

        // Disparar evento de broadcasting
        event(new \App\Events\NuevaNotificacion($notificacion));

        return $notificacion;
    }
}