<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->has_changed_password && !$request->is('change-password*')) {
            return redirect()->route('password.change.form')
                ->with('info', 'For security, please change your password before proceeding.');
        }

        return $next($request);
    }
}
