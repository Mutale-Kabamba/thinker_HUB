<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'payments/webhook',
            'payments/webhook/*',
            'api/payments/webhook/*',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, Request $request) {
            if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Your session timed out. Please try again.',
                    'csrf_token' => csrf_token(),
                ], 419);
            }

            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation', '_token'))
                ->with('status', 'Your session timed out due to inactivity. Please submit again.');
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('teach/instructor-overview')) {
                return null;
            }

            if (! auth()->check()) {
                return null;
            }

            $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : null;

            if ($statusCode !== 403) {
                return null;
            }

            $user = auth()->user();

            if ($user?->isAdmin()) {
                return redirect('/manage');
            }

            if ($user?->isInstructor() && $user->is_active) {
                return redirect('/teach/instructor-overview');
            }

            return redirect('/learn/overview');
        });
    })->create();
