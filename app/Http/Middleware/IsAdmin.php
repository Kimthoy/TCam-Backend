<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role ?? null, ['admin', 'super-admin'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return $next($request);
    }
}
