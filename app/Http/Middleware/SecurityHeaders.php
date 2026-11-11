<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; ".
            "img-src 'self' data: https://covers.openlibrary.org; font-src 'self'; ".
            "connect-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'");
        return $response;
    }
}
