<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\IPSMiddleware;
use App\Http\Middleware\MedicoMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RefreshDataMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['sidebar_state']);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            RefreshDataMiddleware::class,
        ]);

        // Registrar middleware personalizado
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'medico' => MedicoMiddleware::class,
            'ips' => IPSMiddleware::class,
            'guest' => RedirectIfAuthenticated::class,
            'auth' => Authenticate::class,
            'check.permission' => CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
