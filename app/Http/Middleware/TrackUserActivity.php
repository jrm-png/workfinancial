<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            auth()->user()->update([
                'last_activity' => now(),
            ]);
        }

        return $next($request);
    }
}