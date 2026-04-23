<?php

namespace App\Mcp\Tools;

use App\Mcp\Tool;
use App\Models\Ticket;
use App\Models\User;

class ListTicketsTool implements Tool
{
    public function name(): string
    {
        return 'list_tickets';
    }

    public function description(): string
    {
        return 'List tickets the user can access. Filter by project, status, assignee, or free-text code/name search. Returns id, code, name, status, priority, type, owner, responsible, due_date, updated_at.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'project_id' => ['type' => 'integer', 'description' => 'Filter by project id.'],
                'status_id' => ['type' => 'integer', 'description' => 'Filter by status id.'],
                'assignee_id' => ['type' => 'integer', 'description' => 'Filter by owner or responsible user id.'],
                'mine' => ['type' => 'boolean', 'description' => 'Only return tickets owned by or assigned to the current user.'],
                'search' => ['type' => 'string', 'description' => 'Free-text match against code and name.'],
                'limit' => ['type' => 'integer', 'description' => 'Max results (default 25, max 100).'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function execute(User $user, array $args): array
    {
        $limit = min(max((int) ($args['limit'] ?? 25), 1), 100);

        $query = Ticket::query()
            ->with(['owner:id,name', 'responsible:id,name', 'status:id,name,color', 'priority:id,name,color', 'type:id,name', 'project:id,name,ticket_prefix'])
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhere('responsible_id', $user->id)
                    ->orWhereHas('project', function ($pq) use ($user) {
                        $pq->where('owner_id', $user->id)
                            ->orWhereHas('users', fn ($uq) => $uq->where('users.id', $user->id));
                    });
            });

        if (! empty($args['project_id'])) {
            $query->where('project_id', (int) $args['project_id']);
        }
        if (! empty($args['status_id'])) {
            $query->where('status_id', (int) $args['status_id']);
        }
        if (! empty($args['assignee_id'])) {
            $aid = (int) $args['assignee_id'];
            $query->where(fn ($q) => $q->where('owner_id', $aid)->orWhere('responsible_id', $aid));
        }
        if (! empty($args['mine'])) {
            $query->where(fn ($q) => $q->where('owner_id', $user->id)->orWhere('responsible_id', $user->id));
        }
        if (! empty($args['search'])) {
            $terms = preg_split('/[\s,;]+/', trim($args['search'])) ?: [];
            $terms = array_values(array_filter($terms, fn ($t) => $t !== ''));
            if (count($terms) > 1) {
                $query->where(function ($q) use ($terms) {
                    foreach ($terms as $t) {
                        $q->orWhere('code', 'like', "%{$t}%");
                    }
                });
            } elseif (count($terms) === 1) {
                $t = $terms[0];
                $query->where(fn ($q) => $q->where('code', 'like', "%{$t}%")->orWhere('name', 'like', "%{$t}%"));
            }
        }

        $tickets = $query->orderByDesc('updated_at')->limit($limit)->get();

        return [
            'count' => $tickets->count(),
            'tickets' => $tickets->map(fn ($t) => [
                'id' => $t->id,
                'code' => $t->code,
                'name' => $t->name,
                'project' => $t->project ? ['id' => $t->project->id, 'name' => $t->project->name] : null,
                'status' => $t->status ? ['id' => $t->status->id, 'name' => $t->status->name] : null,
                'priority' => $t->priority ? ['id' => $t->priority->id, 'name' => $t->priority->name] : null,
                'type' => $t->type ? ['id' => $t->type->id, 'name' => $t->type->name] : null,
                'owner' => $t->owner ? ['id' => $t->owner->id, 'name' => $t->owner->name] : null,
                'responsible' => $t->responsible ? ['id' => $t->responsible->id, 'name' => $t->responsible->name] : null,
                'due_date' => optional($t->due_date)->toIso8601String(),
                'updated_at' => optional($t->updated_at)->toIso8601String(),
            ])->values(),
        ];
    }
}
