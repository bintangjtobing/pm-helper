<?php

namespace App\Mcp\Tools;

use App\Mcp\Tool;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;

class CreateTicketTool implements Tool
{
    public function name(): string
    {
        return 'create_ticket';
    }

    public function description(): string
    {
        return 'Create a new ticket in a project the user can access. Status/type/priority default to project/system defaults when omitted. Returns the created ticket id + code.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['project_id', 'name', 'content'],
            'properties' => [
                'project_id' => ['type' => 'integer'],
                'name' => ['type' => 'string'],
                'content' => ['type' => 'string', 'description' => 'Description (markdown or HTML).'],
                'responsible_id' => ['type' => 'integer', 'description' => 'Assignee user id.'],
                'type_id' => ['type' => 'integer'],
                'priority_id' => ['type' => 'integer'],
                'status_id' => ['type' => 'integer'],
                'due_date' => ['type' => 'string', 'description' => 'ISO date or datetime.'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function execute(User $user, array $args): array
    {
        if (! $user->can('Create ticket')) {
            throw new \RuntimeException('You do not have permission to create tickets.');
        }

        $project = Project::find((int) ($args['project_id'] ?? 0));
        if (! $project) {
            throw new \RuntimeException('Project not found.');
        }
        $hasAccess = $project->owner_id === $user->id
            || $project->users()->where('users.id', $user->id)->exists();
        if (! $hasAccess) {
            throw new \RuntimeException('Access denied to this project.');
        }

        $data = [
            'name' => trim((string) ($args['name'] ?? '')),
            'content' => (string) ($args['content'] ?? ''),
            'project_id' => $project->id,
            'owner_id' => $user->id,
            'responsible_id' => isset($args['responsible_id']) ? (int) $args['responsible_id'] : null,
            'type_id' => isset($args['type_id']) ? (int) $args['type_id'] : TicketType::where('is_default', true)->value('id'),
            'priority_id' => isset($args['priority_id']) ? (int) $args['priority_id'] : TicketPriority::where('is_default', true)->value('id'),
            'status_id' => isset($args['status_id']) ? (int) $args['status_id'] : TicketStatus::where('is_default', true)->value('id'),
            'due_date' => ! empty($args['due_date']) ? $args['due_date'] : null,
        ];

        if ($data['name'] === '' || $data['content'] === '') {
            throw new \RuntimeException('name and content are required.');
        }

        $ticket = Ticket::create($data);
        $ticket->load(['status:id,name', 'project:id,name,ticket_prefix']);

        return [
            'id' => $ticket->id,
            'code' => $ticket->code,
            'name' => $ticket->name,
            'project' => ['id' => $ticket->project->id, 'name' => $ticket->project->name],
            'status' => $ticket->status ? ['id' => $ticket->status->id, 'name' => $ticket->status->name] : null,
            'message' => 'Ticket created.',
        ];
    }
}
