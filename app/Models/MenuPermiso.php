<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuPermiso extends Model
{
    use HasFactory;

    protected $table = 'menu_permisos';

    protected $fillable = [
        'menu_item',
        'rol',
        'visible',
        'orden'
    ];

    protected $casts = [
        'visible' => 'boolean'
    ];

    // Obtener menú visible para un rol específico
    public static function menuParaRol($rol)
    {
        return self::where('rol', $rol)
                   ->where('visible', true)
                   ->orderBy('orden')
                   ->pluck('menu_item')
                   ->toArray();
    }

    // Configurar permisos por defecto
    public static function configurarDefecto()
    {
        $menus = [
            'dashboard' => ['administrador' => 1, 'medico' => 1, 'ips' => 1],
            'referencias' => ['administrador' => 2, 'medico' => 2, 'ips' => 0],
            'solicitar' => ['administrador' => 0, 'medico' => 0, 'ips' => 2],
            'seguimiento' => ['administrador' => 3, 'medico' => 3, 'ips' => 0],
            'reportes' => ['administrador' => 4, 'medico' => 0, 'ips' => 0],
            'configuracion' => ['administrador' => 5, 'medico' => 0, 'ips' => 0],
            'notificaciones' => ['administrador' => 6, 'medico' => 4, 'ips' => 3]
        ];

        foreach ($menus as $menu => $roles) {
            foreach ($roles as $rol => $orden) {
                self::updateOrCreate(
                    ['menu_item' => $menu, 'rol' => $rol],
                    ['visible' => $orden > 0, 'orden' => $orden]
                );
            }
        }
    }
}