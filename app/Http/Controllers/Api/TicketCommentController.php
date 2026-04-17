<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketCommentResource;
use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketCommentController extends Controller
{
    public function index(Request $request, Ticket $ticket): JsonResponse
    {
        abort_unless($request->user()->can('view', $ticket), 403);

        $comments = $ticket->comments()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'data' => TicketCommentResource::collection($comments),
        ]);
    }

    public function store(Request $request, Ticket $ticket): JsonResponse
    {
        abort_unless($request->user()->can('view', $ticket), 403);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment = $ticket->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        $comment->load('user');

        return response()->json([
            'data' => new TicketCommentResource($comment),
        ], 201);
    }

    public function destroy(Request $request, TicketComment $comment): JsonResponse
    {
        $user = $request->user();
        abort_unless($comment->user_id === $user->id || $user->can('update', $comment->ticket), 403);

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}
