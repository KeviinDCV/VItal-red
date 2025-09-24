<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionUsuario extends Model
{
    use HasFactory;

    protected $table = 'configuracion_usuarios';

    protected $fillable = [
        'user_id',
        'notificaciones',
        'privacidad',
        'interfaz'
    ];

    protected $casts = [
        'notificaciones' => 'array',
        'privacidad' => 'array',
        'interfaz' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function obtenerConfiguracion($userId)
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'notificaciones' => [
                    'email_nuevas_referencias' => true,
                    'email_cambios_estado' => true,
                    'email_recordatorios' => false,
                    'sms_urgencias' => true,
                    'sms_recordatorios' => false,
                    'push_tiempo_real' => true,
                    'push_alertas_criticas' => true,
                    'frecuencia_resumen' => 'diario',
                    'horario_no_molestar_inicio' => '22:00',
                    'horario_no_molestar_fin' => '07:00'
                ],
                'privacidad' => [
                    'mostrar_perfil_publico' => true,
                    'compartir_estadisticas' => true,
                    'permitir_contacto_directo' => true,
                    'mostrar_estado_online' => true,
                    'historial_actividad_visible' => false,
                    'datos_anonimos_investigacion' => true
                ],
                'interfaz' => [
                    'tema' => 'claro',
                    'idioma' => 'es',
                    'zona_horaria' => 'America/Bogota',
                    'formato_fecha' => 'dd/mm/yyyy',
                    'formato_hora' => '24h',
                    'densidad_interfaz' => 'normal',
                    'mostrar_ayuda_contextual' => true
                ]
            ]
        );
    }
}