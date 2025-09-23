<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IPSMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'ips') {
            abort(403, 'Acceso denegado. Solo usuarios IPS pueden acceder a esta sección.');
        }

        return $next($request);
    }
}