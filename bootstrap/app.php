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

        // Register middleware aliases (use simple alias keys - no colon)
        $middleware->alias([
            'auth'       => \Illuminate\Auth\Middleware\Authenticate::class,
           
            'throttle'   => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'is_admin'   => \App\Http\Middleware\IsAdmin::class,
        ]);

        $middleware->api([
          
            // replace 'throttle:api' with a numeric limit:
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    ->create();
