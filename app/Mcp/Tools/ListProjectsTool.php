<?php

namespace App\Mcp\Tools;

use App\Mcp\Tool;
use App\Models\Project;
use App\Models\User;

class ListProjectsTool implements Tool
{
    public function name(): string
    {
        return 'list_projects';
    }

    public function description(): string
    {
        return 'List projects the current user can access (owner or member). Helper to resolve project_id for other tools.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'search' => ['type' => 'string', 'description' => 'Free-text match on name or ticket_prefix.'],
                'limit' => ['type' => 'integer', 'description' => 'Max results (default 50, max 200).'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function execute(User $user, array $args): array
    {
        $limit = min(max((int) ($args['limit'] ?? 50), 1), 200);

        $query = Project::query()
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('users', fn ($inner) => $inner->where('users.id', $user->id));
            });

        if (! empty($args['search'])) {
            $term = trim((string) $args['search']);
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('ticket_prefix', 'like', "%{$term}%"));
        }

        $projects = $query->orderBy('name')->limit($limit)->get();

        return [
            'count' => $projects->count(),
            'projects' => $projects->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'ticket_prefix' => $p->ticket_prefix,
                'type' => $p->type,
            ])->values(),
        ];
    }
}
