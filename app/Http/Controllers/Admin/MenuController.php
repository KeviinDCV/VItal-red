<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuPermiso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index()
    {
        $permisos = MenuPermiso::orderBy('rol')->orderBy('orden')->get()->groupBy('rol');
        
        return Inertia::render('Admin/ConfigurarMenu', [
            'permisos' => $permisos
        ]);
    }

    public function actualizar(Request $request)
    {
        $request->validate([
            'permisos' => 'required|array',
            'permisos.*.menu_item' => 'required|string',
            'permisos.*.rol' => 'required|in:administrador,medico,ips',
            'permisos.*.visible' => 'required|boolean',
            'permisos.*.orden' => 'required|integer'
        ]);

        foreach ($request->permisos as $permiso) {
            MenuPermiso::updateOrCreate(
                ['menu_item' => $permiso['menu_item'], 'rol' => $permiso['rol']],
                ['visible' => $permiso['visible'], 'orden' => $permiso['orden']]
            );
        }

        return back()->with('success', 'Permisos de menú actualizados correctamente');
    }

    public function restaurarDefecto()
    {
        MenuPermiso::configurarDefecto();
        return back()->with('success', 'Configuración de menú restaurada a valores por defecto');
    }
}