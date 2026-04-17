<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

/**
 * Mobile-only Pusher channel authentication.
 *
 * Pusher clients hit this when they subscribe to a private channel to get
 * back a signed auth string. The default /broadcasting/auth route runs web
 * (session+CSRF) middleware, which the mobile app cannot satisfy — so we
 * expose a parallel endpoint behind the Sanctum bearer guard.
 */
class BroadcastAuthController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // Broadcast::auth() inspects channel_name + socket_id in the request,
        // runs our callbacks in routes/channels.php against the authenticated
        // user, and returns the signed payload Pusher expects.
        $response = Broadcast::auth($request);

        // If the response is already a proper JsonResponse (success), return it.
        if ($response instanceof JsonResponse) {
            return $response;
        }

        // For string payloads, wrap in JsonResponse.
        return response()->json(json_decode($response, true));
    }
}
