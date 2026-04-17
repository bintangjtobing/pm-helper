<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiscussionReplyResource;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscussionReplyController extends Controller
{
    public function store(Request $request, Discussion $discussion): JsonResponse
    {
        $this->authorizeProjectAccess($request, $discussion);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $reply = $discussion->replies()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        $reply->load('user');

        return response()->json([
            'data' => new DiscussionReplyResource($reply),
        ], 201);
    }

    public function destroy(Request $request, DiscussionReply $reply): JsonResponse
    {
        $user = $request->user();
        abort_unless($reply->user_id === $user->id, 403, 'You can only delete your own replies.');

        $reply->delete();

        return response()->json(['message' => 'Reply deleted']);
    }

    protected function authorizeProjectAccess(Request $request, Discussion $discussion): void
    {
        $project = $discussion->project;
        if (! $project) abort(404);

        $user = $request->user();
        $hasAccess = $project->owner_id === $user->id
            || $project->users()->where('users.id', $user->id)->exists();

        abort_unless($hasAccess, 403, 'You do not have access to this project.');
    }
}
