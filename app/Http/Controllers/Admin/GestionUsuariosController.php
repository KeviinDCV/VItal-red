<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class GestionUsuariosController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Filtros
        if ($request->busqueda) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->busqueda}%")
                  ->orWhere('email', 'LIKE', "%{$request->busqueda}%");
            });
        }

        if ($request->rol) {
            $query->where('role', $request->rol);
        }

        if ($request->estado) {
            $query->where('status', $request->estado);
        }

        $usuarios = $query->orderBy('created_at', 'desc')->paginate(20);

        $estadisticas = [
            'total_usuarios' => User::count(),
            'usuarios_activos' => User::where('status', 'activo')->count(),
            'usuarios_inactivos' => User::where('status', 'inactivo')->count(),
            'nuevos_este_mes' => User::whereMonth('created_at', now()->month)->count()
        ];

        return Inertia::render('admin/GestionUsuarios', [
            'usuarios' => $usuarios,
            'estadisticas' => $estadisticas
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:administrador,medico,ips,jefe-urgencias'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'activo'
        ]);

        return redirect()->back()->with('success', 'Usuario creado exitosamente');
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $usuario->id,
            'role' => 'required|in:administrador,medico,ips,jefe-urgencias'
        ]);

        $usuario->update($request->only(['name', 'email', 'role']));

        return redirect()->back()->with('success', 'Usuario actualizado exitosamente');
    }

    public function toggleStatus(User $usuario)
    {
        $usuario->update([
            'status' => $usuario->status === 'activo' ? 'inactivo' : 'activo'
        ]);

        return redirect()->back()->with('success', 'Estado del usuario actualizado');
    }

    public function destroy(User $usuario)
    {
        $usuario->delete();
        return redirect()->back()->with('success', 'Usuario eliminado exitosamente');
    }
}