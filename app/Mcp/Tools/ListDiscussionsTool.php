<?php

namespace App\Mcp\Tools;

use App\Mcp\Tool;
use App\Models\Discussion;
use App\Models\User;

class ListDiscussionsTool implements Tool
{
    public function name(): string
    {
        return 'list_discussions';
    }

    public function description(): string
    {
        return 'List discussions the user can access (scoped to projects the user is owner or member of). Filter by project and/or status.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'project_id' => ['type' => 'integer'],
                'status' => ['type' => 'string', 'description' => 'e.g. open, resolved.'],
                'search' => ['type' => 'string', 'description' => 'Free-text match on title.'],
                'limit' => ['type' => 'integer', 'description' => 'Max results (default 25, max 100).'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function execute(User $user, array $args): array
    {
        $limit = min(max((int) ($args['limit'] ?? 25), 1), 100);

        $query = Discussion::query()
            ->with(['user:id,name', 'project:id,name', 'ticket:id,code,name'])
            ->withCount('replies')
            ->whereHas('project', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('users', fn ($inner) => $inner->where('users.id', $user->id));
            });

        if (! empty($args['project_id'])) {
            $query->where('project_id', (int) $args['project_id']);
        }
        if (! empty($args['status'])) {
            $query->where('status', (string) $args['status']);
        }
        if (! empty($args['search'])) {
            $query->where('title', 'like', '%' . trim((string) $args['search']) . '%');
        }

        $discussions = $query->orderByDesc('created_at')->limit($limit)->get();

        return [
            'count' => $discussions->count(),
            'discussions' => $discussions->map(fn ($d) => [
                'id' => $d->id,
                'title' => $d->title,
                'status' => $d->status,
                'priority' => $d->priority,
                'project' => $d->project ? ['id' => $d->project->id, 'name' => $d->project->name] : null,
                'ticket' => $d->ticket ? ['id' => $d->ticket->id, 'code' => $d->ticket->code, 'name' => $d->ticket->name] : null,
                'author' => $d->user ? ['id' => $d->user->id, 'name' => $d->user->name] : null,
                'replies_count' => (int) ($d->replies_count ?? 0),
                'created_at' => optional($d->created_at)->toIso8601String(),
            ])->values(),
        ];
    }
}
