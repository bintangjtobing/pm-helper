<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BroadcastAuthController;
use App\Http\Controllers\Api\DailyReportController;
use App\Http\Controllers\Api\DiscussionController;
use App\Http\Controllers\Api\DiscussionReplyController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TicketCommentController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketLookupController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WeeklyReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Mobile App
|--------------------------------------------------------------------------
| Consumed by the PMHelper mobile app (Expo/React Native). Web dashboard
| continues to use Filament/Livewire — these routes are additive.
*/

// Public
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    // Legacy alias — kept for any existing callers
    Route::get('user', [AuthController::class, 'me']);

    // Projects
    Route::get('projects', [ProjectController::class, 'index']);
    Route::get('projects/{project}', [ProjectController::class, 'show']);
    Route::get('projects/{project}/tickets', [TicketController::class, 'indexByProject']);

    // Tickets
    Route::post('tickets', [TicketController::class, 'store']);
    Route::get('tickets/{ticket}', [TicketController::class, 'show']);
    Route::patch('tickets/{ticket}', [TicketController::class, 'update']);
    Route::delete('tickets/{ticket}', [TicketController::class, 'destroy']);
    Route::post('tickets/{ticket}/move', [TicketController::class, 'move']);

    // Ticket comments
    Route::get('tickets/{ticket}/comments', [TicketCommentController::class, 'index']);
    Route::post('tickets/{ticket}/comments', [TicketCommentController::class, 'store']);
    Route::delete('ticket-comments/{comment}', [TicketCommentController::class, 'destroy']);

    // Ticket lookups (for form dropdowns)
    Route::get('ticket-statuses', [TicketLookupController::class, 'statuses']);
    Route::get('ticket-types', [TicketLookupController::class, 'types']);
    Route::get('ticket-priorities', [TicketLookupController::class, 'priorities']);

    // Daily Reports
    Route::get('daily-reports', [DailyReportController::class, 'index']);
    Route::post('daily-reports', [DailyReportController::class, 'store']);
    Route::get('daily-reports/{dailyReport}', [DailyReportController::class, 'show']);
    Route::patch('daily-reports/{dailyReport}', [DailyReportController::class, 'update']);
    Route::delete('daily-reports/{dailyReport}', [DailyReportController::class, 'destroy']);

    // Weekly Reports
    Route::get('weekly-reports', [WeeklyReportController::class, 'index']);
    Route::post('weekly-reports', [WeeklyReportController::class, 'store']);
    Route::get('weekly-reports/{weeklyReport}', [WeeklyReportController::class, 'show']);
    Route::patch('weekly-reports/{weeklyReport}', [WeeklyReportController::class, 'update']);
    Route::delete('weekly-reports/{weeklyReport}', [WeeklyReportController::class, 'destroy']);

    // Discussions
    Route::get('discussions', [DiscussionController::class, 'index']);
    Route::post('discussions', [DiscussionController::class, 'store']);
    Route::get('discussions/{discussion}', [DiscussionController::class, 'show']);
    Route::patch('discussions/{discussion}', [DiscussionController::class, 'update']);
    Route::delete('discussions/{discussion}', [DiscussionController::class, 'destroy']);

    // Discussion replies
    Route::post('discussions/{discussion}/replies', [DiscussionReplyController::class, 'store']);
    Route::delete('discussion-replies/{reply}', [DiscussionReplyController::class, 'destroy']);

    // Users
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{user}', [UserController::class, 'show']);

    // Pusher channel authentication for private/presence subscriptions
    Route::post('broadcasting/auth', BroadcastAuthController::class);
});
