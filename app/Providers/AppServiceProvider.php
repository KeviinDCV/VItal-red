<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\MenuPermiso;
use App\Models\UserPermission;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Compartir permisos de menú con todas las vistas
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                
                // Obtener permisos de menú del usuario
                $menuPermisos = MenuPermiso::menuParaRol($user->role);
                
                // Obtener permisos específicos del usuario
                $userPermissions = UserPermission::where('user_id', $user->id)
                    ->where('granted', true)
                    ->pluck('permission')
                    ->toArray();
                
                $view->with([
                    'menuPermisos' => $menuPermisos,
                    'userPermissions' => $userPermissions
                ]);
            }
        });
    }
}
