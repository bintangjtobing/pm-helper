<?php

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Http\Controllers\RoadMap\DataController;
use App\Http\Controllers\Auth\OidcAuthController;
use App\Http\Controllers\Messenger\MessengerAttachmentController;
use App\Http\Controllers\PublicFeedbackController;

// Share ticket
Route::get('/tickets/share/{ticket:code}', function (Ticket $ticket) {
    return redirect()->to(route('filament.resources.tickets.view', $ticket));
})->name('filament.resources.tickets.share');

// Public customer feedback form (no auth, opt-in per project)
Route::middleware(['web', 'throttle:public-feedback'])->group(function () {
    Route::get('/feedback/{token}', [PublicFeedbackController::class, 'show'])->name('public.feedback.show');
    Route::post('/feedback/{token}', [PublicFeedbackController::class, 'store'])->name('public.feedback.store');
    Route::get('/feedback/{token}/thanks', [PublicFeedbackController::class, 'thanks'])->name('public.feedback.thanks');
});

// Validate an account
Route::get('/validate-account/{user:creation_token}', function (User $user) {
    return view('validate-account', compact('user'));
})
    ->name('validate-account')
    ->middleware([
        'web',
        DispatchServingFilamentEvent::class
    ]);

// Login default redirection
Route::redirect('/login-redirect', '/login')->name('login');

// Road map JSON data
Route::get('road-map/data/{project}', [DataController::class, 'data'])
    ->middleware(['verified', 'auth'])
    ->name('road-map.data');

Route::name('oidc.')
    ->prefix('oidc')
    ->group(function () {
        Route::get('redirect', [OidcAuthController::class, 'redirect'])->name('redirect');
        Route::get('callback', [OidcAuthController::class, 'callback'])->name('callback');
    });

// Messenger attachment download/preview (private, auth-gated)
Route::middleware(['web', 'auth'])
    ->prefix('messenger/attachments')
    ->name('messenger.attachments.')
    ->group(function () {
        Route::get('{message}/{attachment}/download', [MessengerAttachmentController::class, 'download'])
            ->name('download');
        Route::get('{message}/{attachment}/preview', [MessengerAttachmentController::class, 'preview'])
            ->name('preview');
    });

// Standalone full-screen Jitsi meeting page. Opens in a new tab from messenger,
// broadcasts lifecycle events back to the main PMHelper tab via BroadcastChannel.
Route::middleware(['web', 'auth'])
    ->get('/dm/meet/{slug}', function (string $slug) {
        abort_unless(preg_match('/^pmhelper-[a-z0-9]+$/i', $slug), 404);
        return view('messenger.meet-tab', [
            'slug' => $slug,
            'meetUser' => auth()->user(),
            'role' => request('role', 'joiner'),
            'conversationId' => (int) request('convo', 0),
            'meetingMessageId' => (int) request('msg', 0),
        ]);
    })
    ->name('messenger.meet.tab');

// ─── MCP OAuth 2.0 (RFC 6749 + RFC 7591 + PKCE) ──────────────────────────
// Lets Claude Desktop and other MCP clients obtain a Sanctum access token
// without exposing PMHelper internals or long-lived credentials.
// The issued access_token lives in the same personal_access_tokens table
// used by /api/mcp, so no change to the MCP controller is required.
// /oauth/* paths are canonical (advertised by the metadata endpoint). The
// /mcp/* paths are unnamed aliases kept for robustness. Filament Socialite's
// catch-all /oauth/{provider} is constrained in RouteServiceProvider so it
// no longer swallows names like "authorize", "register", or "token".
Route::get('/.well-known/oauth-authorization-server',
    [\App\Http\Controllers\Mcp\OAuthController::class, 'metadata'])
    ->name('oauth.metadata');
// RFC 9728 — Protected Resource Metadata. Claude Desktop probes this before
// discovery; answering lets it find the authorization server cleanly.
Route::get('/.well-known/oauth-protected-resource',
    [\App\Http\Controllers\Mcp\OAuthController::class, 'protectedResource']);
Route::get('/.well-known/oauth-protected-resource/api/mcp',
    [\App\Http\Controllers\Mcp\OAuthController::class, 'protectedResource']);

// Canonical OAuth endpoints (named).
Route::post('/oauth/register',
    [\App\Http\Controllers\Mcp\OAuthController::class, 'register'])
    ->name('oauth.register');
Route::post('/oauth/token',
    [\App\Http\Controllers\Mcp\OAuthController::class, 'token'])
    ->name('oauth.token');
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/oauth/authorize',
        [\App\Http\Controllers\Mcp\OAuthController::class, 'showConsent'])
        ->name('oauth.authorize');
    Route::post('/oauth/authorize',
        [\App\Http\Controllers\Mcp\OAuthController::class, 'approve'])
        ->name('oauth.approve');
});

// Unnamed /mcp/* aliases for clients that still hit the previous path.
Route::post('/mcp/register',
    [\App\Http\Controllers\Mcp\OAuthController::class, 'register']);
Route::post('/mcp/token',
    [\App\Http\Controllers\Mcp\OAuthController::class, 'token']);
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/mcp/authorize',
        [\App\Http\Controllers\Mcp\OAuthController::class, 'showConsent']);
    Route::post('/mcp/authorize',
        [\App\Http\Controllers\Mcp\OAuthController::class, 'approve']);
});

// Auto-detect timezone from browser
Route::post('/user/timezone', function (\Illuminate\Http\Request $request) {
    if (auth()->check() && $request->has('timezone')) {
        $tz = $request->input('timezone');
        // Validate timezone
        if (in_array($tz, timezone_identifiers_list())) {
            auth()->user()->update(['timezone' => $tz]);
            return response()->json(['ok' => true]);
        }
    }
    return response()->json(['ok' => false], 400);
})->middleware('auth')->name('user.timezone');
