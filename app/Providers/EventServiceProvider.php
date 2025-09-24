<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\CriticalAlertCreated;
use App\Events\NuevaNotificacion;
use App\Listeners\SendCriticalAlertNotification;
use App\Listeners\LogAuditEvent;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        
        Login::class => [
            LogAuditEvent::class,
        ],
        
        Logout::class => [
            LogAuditEvent::class,
        ],
        
        CriticalAlertCreated::class => [
            SendCriticalAlertNotification::class,
        ],
        
        NuevaNotificacion::class => [
            // Listeners para notificaciones
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}