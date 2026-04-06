<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApplyUserTimezone
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $timezone = auth()->user()->timezone ?? config('app.timezone');

            try {
                date_default_timezone_set($timezone);
                config(['app.timezone' => $timezone]);
            } catch (\Exception $e) {
                // Fallback to app default if invalid timezone
            }
        }

        return $next($request);
    }
}
