<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserPermission;
use App\Models\MenuPermiso;
use Symfony\Component\HttpFoundation\Response;

class CheckViewPermission
{
    public function handle(Request $request, Closure $next, string $view = null): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Administradores tienen acceso completo
        if ($user->role === 'administrador') {
            return $next($request);
        }

        // Si no se especifica vista, permitir acceso
        if (!$view) {
            return $next($request);
        }

        // Verificar si el usuario tiene permiso para esta vista
        $hasPermission = $this->checkUserViewPermission($user, $view);

        if (!$hasPermission) {
            abort(403, 'No tienes permisos para acceder a esta vista.');
        }

        return $next($request);
    }

    private function checkUserViewPermission($user, $view): bool
    {
        // Verificar permisos específicos del usuario
        $userPermission = UserPermission::where('user_id', $user->id)
            ->where('permission', $view)
            ->where('granted', true)
            ->exists();

        if ($userPermission) {
            return true;
        }

        // Verificar permisos por patrón (ej: admin.*, medico.*)
        $patternPermissions = UserPermission::where('user_id', $user->id)
            ->where('granted', true)
            ->get();

        foreach ($patternPermissions as $permission) {
            if ($this->matchesPattern($view, $permission->permission)) {
                return true;
            }
        }

        // Verificar permisos por defecto del rol
        $defaultPermissions = UserPermission::getDefaultPermissions($user->role);
        
        foreach ($defaultPermissions as $defaultPerm) {
            if ($this->matchesPattern($view, $defaultPerm)) {
                return true;
            }
        }

        // Verificar configuración de menú
        $menuVisible = MenuPermiso::where('rol', $user->role)
            ->where('menu_item', $view)
            ->where('visible', true)
            ->exists();

        return $menuVisible;
    }

    private function matchesPattern($view, $pattern): bool
    {
        // Convertir patrón con * a regex
        $regex = str_replace(['*', '.'], ['.*', '\\.'], $pattern);
        return preg_match('/^' . $regex . '$/', $view);
    }
}