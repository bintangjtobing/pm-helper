<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UpdateLastSeen
{
    /**
     * Touch the authenticated user's last_seen_at column at most once per 60 seconds.
     * Used as a fallback "Last seen" indicator when the Pusher presence channel is
     * unavailable (e.g. user has disconnected from WebSocket but is still browsing
     * via polling).
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            $cacheKey = 'messenger_last_seen_user_' . $user->id;

            if (! Cache::has($cacheKey)) {
                $user->forceFill(['last_seen_at' => now()])->saveQuietly();
                Cache::put($cacheKey, true, now()->addSeconds(60));
            }
        }

        return $next($request);
    }
}
