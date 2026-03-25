<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Per-user locale preference takes priority over app-wide setting
        if (auth()->check() && auth()->user()->locale) {
            app()->setLocale(auth()->user()->locale);
        } else {
            app()->setLocale(config('app.locale'));
        }
        return $next($request);
    }
}
