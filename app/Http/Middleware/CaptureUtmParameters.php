<?php

namespace App\Http\Middleware;

use App\Services\UtmAttributionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CaptureUtmParameters
{
    public function __construct(private readonly UtmAttributionService $utmService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && ! $request->expectsJson()) {
            $captured = $this->utmService->parseQueryParameters($request);

            if ($captured !== null) {
                if ($request->hasSession()) {
                    $request->session()->put(UtmAttributionService::COOKIE_NAME, $captured);
                }

                Cookie::queue(
                    Cookie::make(
                        UtmAttributionService::COOKIE_NAME,
                        json_encode($captured, JSON_UNESCAPED_SLASHES),
                        60 * 24 * 30, // 30 days
                        '/',
                        null,
                        $request->isSecure(),
                        false, // readable by lightweight JS localStorage mirror if needed
                        false,
                        'Lax'
                    )
                );
            }
        }

        return $next($request);
    }
}
