<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RefreshDataMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Limpiar cache para obtener datos frescos
        if ($request->header('X-Refresh-Data') === 'true') {
            Cache::flush();
        }

        $response = $next($request);

        // Agregar headers para evitar cache del navegador
        if ($request->isMethod('GET') && $request->expectsJson()) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}