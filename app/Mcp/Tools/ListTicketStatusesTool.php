<?php

namespace App\Mcp\Tools;

use App\Mcp\Tool;
use App\Models\Project;
use App\Models\TicketStatus;
use App\Models\User;

class ListTicketStatusesTool implements Tool
{
    public function name(): string
    {
        return 'list_ticket_statuses';
    }

    public function description(): string
    {
        return 'List ticket statuses. Optionally scope to a projects custom statuses (when project.status_type="custom"), otherwise returns global statuses.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'project_id' => ['type' => 'integer', 'description' => 'Scope to this projects statuses.'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function execute(User $user, array $args): array
    {
        $query = TicketStatus::query()->orderBy('order');

        if (! empty($args['project_id'])) {
            $project = Project::find((int) $args['project_id']);
            if ($project && $project->status_type === 'custom') {
                $query->where('project_id', $project->id);
            } else {
                $query->whereNull('project_id');
            }
        } else {
            $query->whereNull('project_id');
        }

        $statuses = $query->get(['id', 'name', 'color', 'is_default', 'order', 'role_group']);

        return [
            'count' => $statuses->count(),
            'statuses' => $statuses->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'color' => $s->color,
                'is_default' => (bool) $s->is_default,
                'order' => $s->order,
                'role_group' => $s->role_group,
            ])->values(),
        ];
    }
}
