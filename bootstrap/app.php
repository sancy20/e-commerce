<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register your custom route middleware alias here
        $middleware->alias([
            'is_vendor' => \App\Http\Middleware\IsVendor::class, // <-- ADD THIS LINE
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            // You might see other default aliases like 'auth', 'guest', 'signed', etc.
        ]);

        // You can also add global middleware here if needed:
        // $middleware->web(append: [
        //     \App\Http\Middleware\TrustProxies::class,
        // ]);
        // $middleware->api(prepend: [
        //     \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();