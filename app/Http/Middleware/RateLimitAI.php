<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitAI
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'ai_requests:' . ($request->user()?->id ?? $request->ip());
        
        if (RateLimiter::tooManyAttempts($key, 60)) { // 60 requests per minute
            return response()->json([
                'error' => 'Too many AI requests. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
