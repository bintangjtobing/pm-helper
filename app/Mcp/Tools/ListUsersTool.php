<?php

namespace App\Mcp\Tools;

use App\Mcp\Tool;
use App\Models\User;

class ListUsersTool implements Tool
{
    public function name(): string
    {
        return 'list_users';
    }

    public function description(): string
    {
        return 'List users in the organization (id, name, email). Helper to resolve assignee / user_id for other tools.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'search' => ['type' => 'string', 'description' => 'Free-text match on name or email.'],
                'limit' => ['type' => 'integer', 'description' => 'Max results (default 50, max 200).'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function execute(User $user, array $args): array
    {
        $limit = min(max((int) ($args['limit'] ?? 50), 1), 200);

        $query = User::query();
        if (! empty($args['search'])) {
            $term = trim((string) $args['search']);
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%"));
        }

        $users = $query->orderBy('name')->limit($limit)->get(['id', 'name', 'email']);

        return [
            'count' => $users->count(),
            'users' => $users->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ])->values(),
        ];
    }
}
