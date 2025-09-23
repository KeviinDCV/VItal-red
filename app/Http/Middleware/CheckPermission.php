<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserPermission;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Administradores tienen acceso completo
        if ($user->role === 'administrador') {
            return $next($request);
        }

        // Si no se especifica permiso, solo verificar autenticación
        if (!$permission) {
            return $next($request);
        }

        // Verificar permisos específicos
        $hasPermission = UserPermission::where('user_id', $user->id)
            ->where('permission', 'LIKE', $permission . '%')
            ->where('granted', true)
            ->exists();

        // También verificar permisos por rol
        $rolePermissions = UserPermission::getDefaultPermissions($user->role);
        $hasRolePermission = collect($rolePermissions)->contains(function ($perm) use ($permission) {
            return str_starts_with($perm, $permission) || $perm === $permission . '.*';
        });

        if (!$hasPermission && !$hasRolePermission) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}