<?php

use App\Models\MessengerConversation;
use App\Models\Project;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
 * Messenger: private channel per conversation.
 * Only the two participants of the conversation may subscribe.
 */
Broadcast::channel('messenger.conversation.{conversation}', function ($user, MessengerConversation $conversation) {
    return $conversation->hasParticipant((int) $user->id);
});

/*
 * Messenger: presence channel for online status across the team.
 * Returning a payload (not a bool) is required for presence channels.
 * Includes manual status fields so clients can render the right indicator.
 */
/*
 * Kanban: private channel per project.
 * Only project owner or members can subscribe to receive ticket move events.
 */
Broadcast::channel('project.{project}.kanban', function ($user, Project $project) {
    return (int) $project->owner_id === (int) $user->id
        || $project->users()->where('users.id', $user->id)->exists();
});

Broadcast::channel('messenger.online', function ($user) {
    return [
        'id' => (int) $user->id,
        'name' => $user->name,
        'username' => $user->username,
        'avatar' => $user->avatar_url,
        'status' => $user->effectiveStatus(),
        'status_message' => $user->status_message,
        'on_leave_until' => $user->on_leave_until?->toIso8601String(),
    ];
});
