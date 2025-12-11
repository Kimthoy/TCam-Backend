<?php
// app/Providers/RouteServiceProvider.php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->configureRateLimiting();

        // existing route registration...
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            // allow 60 requests per minute per authenticated user, fallback to IP
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}