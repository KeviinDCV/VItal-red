<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    /**
     * Mostrar la lista de usuarios
     */
    public function index()
    {
        $usuarios = User::select('id', 'name', 'email', 'role', 'is_active', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        // Estadísticas
        $stats = [
            'total' => User::count(),
            'administradores' => User::where('role', 'administrador')->count(),
            'medicos' => User::where('role', 'medico')->count(),
            'activos' => User::where('is_active', true)->count(),
        ];

        return Inertia::render('admin/usuarios', [
            'usuarios' => $usuarios,
            'stats' => $stats,
        ]);
    }

    /**
     * Crear un nuevo usuario
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:administrador,medico',
            'is_active' => 'boolean',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->is_active ?? true,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.usuarios')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Actualizar un usuario
     */
    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $usuario->id,
            'role' => 'required|in:administrador,medico',
            'is_active' => 'boolean',
        ]);

        $usuario->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.usuarios')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Cambiar estado de un usuario (activar/desactivar)
     */
    public function toggleStatus(User $usuario)
    {
        $usuario->update([
            'is_active' => !$usuario->is_active,
        ]);

        $status = $usuario->is_active ? 'activado' : 'desactivado';
        return redirect()->route('admin.usuarios')->with('success', "Usuario {$status} exitosamente.");
    }

    /**
     * Eliminar un usuario
     */
    public function destroy(User $usuario)
    {
        // Prevenir que el administrador se elimine a sí mismo
        if ($usuario->id === auth()->id()) {
            return redirect()->route('admin.usuarios')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios')->with('success', 'Usuario eliminado exitosamente.');
    }

    public function permisos(User $usuario)
    {
        $availablePermissions = [
            'admin' => [
                'admin.usuarios.view',
                'admin.usuarios.create', 
                'admin.usuarios.edit',
                'admin.usuarios.delete',
                'admin.referencias.view',
                'admin.referencias.edit',
                'admin.reportes.view',
                'admin.reportes.export',
                'admin.configuracion-ia.view',
                'admin.configuracion-ia.edit'
            ],
            'medico' => [
                'medico.ingresar-registro.view',
                'medico.ingresar-registro.create',
                'medico.consulta-pacientes.view',
                'medico.referencias.view',
                'medico.referencias.edit',
                'medico.seguimiento.view',
                'medico.seguimiento.edit'
            ],
            'ips' => [
                'ips.solicitar.view',
                'ips.solicitar.create',
                'ips.mis-solicitudes.view'
            ],
            'jefe_urgencias' => [
                'admin.reportes.view',
                'admin.estadisticas.view',
                'medico.referencias.view',
                'ips.solicitudes.view'
            ],
            'centro_referencia' => [
                'medico.referencias.view',
                'medico.referencias.edit',
                'medico.seguimiento.view',
                'medico.seguimiento.edit',
                'ips.solicitudes.view',
                'admin.reportes.view'
            ]
        ];

        return Inertia::render('admin/PermisosUsuario', [
            'usuario' => $usuario->load('permissions'),
            'availablePermissions' => $availablePermissions
        ]);
    }

    public function updatePermisos(Request $request, User $usuario)
    {
        $permissions = $request->input('permissions', []);
        
        // Eliminar permisos existentes
        $usuario->permissions()->delete();
        
        // Crear nuevos permisos
        foreach ($permissions as $permission => $granted) {
            if ($granted) {
                $usuario->grantPermission($permission);
            } else {
                $usuario->revokePermission($permission);
            }
        }
        
        return redirect()->route('admin.usuarios')
            ->with('success', 'Permisos actualizados correctamente.');
    }
}
