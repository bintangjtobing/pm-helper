<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
    public function __invoke(Request $request)
    {
        // Broadcast::auth() inspects channel_name + socket_id in the request,
        // runs our callbacks in routes/channels.php against the authenticated
        // user, and returns the signed payload Pusher expects. It can return
        // a JsonResponse, Response, array, or JSON string depending on channel
        // type — so pass it through untouched.
        return Broadcast::auth($request);
    }
}
