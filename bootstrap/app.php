<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))

    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {

        // ✅ Middleware aliases (NO throttle)
        $middleware->alias([
            'auth'     => \Illuminate\Auth\Middleware\Authenticate::class,
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
        ]);

        // ✅ API middleware group (NO throttle)
        $middleware->api([
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    ->create();
