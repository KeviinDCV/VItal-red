<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPermission extends Model
{
    protected $fillable = [
        'user_id',
        'permission',
        'granted'
    ];

    protected $casts = [
        'granted' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Permisos por defecto según rol
    public static function getDefaultPermissions($role)
    {
        $permissions = [
            'administrador' => [
                'admin.*', // Acceso completo
                'medico.*',
                'ips.*',
                'jefe_urgencias.*',
                'centro_referencia.*'
            ],
            'jefe_urgencias' => [
                'admin.reportes.view',
                'admin.estadisticas.view',
                'medico.referencias.view',
                'ips.solicitudes.view'
            ],
            'centro_referencia' => [
                'medico.referencias.*',
                'medico.seguimiento.*',
                'ips.solicitudes.view',
                'admin.reportes.view'
            ],
            'medico' => [
                'medico.ingresar-registro.*',
                'medico.consulta-pacientes.*',
                'medico.referencias.view',
                'medico.seguimiento.*'
            ],
            'ips' => [
                'ips.solicitar.*',
                'ips.mis-solicitudes.*'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}