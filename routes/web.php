<?php

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Http\Controllers\RoadMap\DataController;
use App\Http\Controllers\Auth\OidcAuthController;
use App\Http\Controllers\Messenger\MessengerAttachmentController;

// Share ticket
Route::get('/tickets/share/{ticket:code}', function (Ticket $ticket) {
    return redirect()->to(route('filament.resources.tickets.view', $ticket));
})->name('filament.resources.tickets.share');

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
