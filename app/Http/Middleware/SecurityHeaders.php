<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Append baseline HTTP security headers to all web responses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $response->headers->has('X-Content-Type-Options')) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }

        if (! $response->headers->has('X-Frame-Options')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        if (! $response->headers->has('Referrer-Policy')) {
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        if (! $response->headers->has('Permissions-Policy')) {
            $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(self)');
        }

        if (! $response->headers->has('X-Permitted-Cross-Domain-Policies')) {
            $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        }

        if (! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', "frame-ancestors 'self'; base-uri 'self'; object-src 'none'");
        }

        if ($request->isSecure() && ! $response->headers->has('Strict-Transport-Security')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}

