<?php

namespace App\Mcp\Tools;

use App\Mcp\Tool;
use App\Models\Discussion;
use App\Models\User;

class GetDiscussionTool implements Tool
{
    public function name(): string
    {
        return 'get_discussion';
    }

    public function description(): string
    {
        return 'Get full details of one discussion including all replies.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['id'],
            'properties' => [
                'id' => ['type' => 'integer'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function execute(User $user, array $args): array
    {
        $discussion = Discussion::with([
            'user:id,name,email',
            'project:id,name',
            'ticket:id,code,name',
            'resolvedByUser:id,name',
            'replies' => fn ($q) => $q->with('user:id,name,email')->orderBy('created_at'),
        ])->find((int) ($args['id'] ?? 0));

        if (! $discussion) {
            throw new \RuntimeException('Discussion not found.');
        }

        $project = $discussion->project;
        $hasAccess = $project && ($project->owner_id === $user->id
            || $project->users()->where('users.id', $user->id)->exists());
        if (! $hasAccess) {
            throw new \RuntimeException('Access denied to this discussion.');
        }

        return [
            'id' => $discussion->id,
            'title' => $discussion->title,
            'content' => $discussion->content,
            'status' => $discussion->status,
            'priority' => $discussion->priority,
            'project' => $project ? ['id' => $project->id, 'name' => $project->name] : null,
            'ticket' => $discussion->ticket ? ['id' => $discussion->ticket->id, 'code' => $discussion->ticket->code, 'name' => $discussion->ticket->name] : null,
            'author' => $discussion->user ? ['id' => $discussion->user->id, 'name' => $discussion->user->name, 'email' => $discussion->user->email] : null,
            'resolved_by' => $discussion->resolvedByUser ? ['id' => $discussion->resolvedByUser->id, 'name' => $discussion->resolvedByUser->name] : null,
            'resolved_at' => optional($discussion->resolved_at)->toIso8601String(),
            'created_at' => optional($discussion->created_at)->toIso8601String(),
            'replies_count' => $discussion->replies->count(),
            'replies' => $discussion->replies->map(fn ($r) => [
                'id' => $r->id,
                'author' => $r->user ? ['id' => $r->user->id, 'name' => $r->user->name, 'email' => $r->user->email] : null,
                'content' => $r->content,
                'created_at' => optional($r->created_at)->toIso8601String(),
            ])->values(),
        ];
    }
}
