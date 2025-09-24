<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\SolicitudReferencia;
use App\Policies\SolicitudReferenciaPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        SolicitudReferencia::class => SolicitudReferenciaPolicy::class,
    ];

    public function boot(): void
    {
        // Gates personalizados
        Gate::define('admin-access', function ($user) {
            return $user->role === 'administrador';
        });

        Gate::define('medico-access', function ($user) {
            return in_array($user->role, ['administrador', 'medico']);
        });

        Gate::define('ips-access', function ($user) {
            return in_array($user->role, ['administrador', 'ips']);
        });

        Gate::define('jefe-urgencias-access', function ($user) {
            return in_array($user->role, ['administrador', 'jefe-urgencias']);
        });

        Gate::define('evaluate-solicitud', function ($user, $solicitud) {
            return $user->role === 'medico' && $solicitud->estado === 'PENDIENTE';
        });
    }
}