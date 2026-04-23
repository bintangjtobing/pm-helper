<?php

namespace App\Mcp\Tools;

use App\Mcp\Tool;
use App\Models\Ticket;
use App\Models\User;

class GetTicketTool implements Tool
{
    public function name(): string
    {
        return 'get_ticket';
    }

    public function description(): string
    {
        return 'Get full details of one ticket (by id or code like "wd-51"), including comments with authors. Respects the users access to the ticket.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'description' => 'Ticket id.'],
                'code' => ['type' => 'string', 'description' => 'Ticket code, e.g. "wd-51". Case-insensitive.'],
                'include_comments' => ['type' => 'boolean', 'description' => 'Include all comments (default true).'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function execute(User $user, array $args): array
    {
        $ticket = null;
        if (! empty($args['id'])) {
            $ticket = Ticket::find((int) $args['id']);
        } elseif (! empty($args['code'])) {
            $ticket = Ticket::whereRaw('LOWER(code) = ?', [strtolower(trim((string) $args['code']))])->first();
        }

        if (! $ticket) {
            throw new \RuntimeException('Ticket not found.');
        }

        if (! $user->can('view', $ticket)) {
            throw new \RuntimeException('Access denied to this ticket.');
        }

        $ticket->load(['owner:id,name,email', 'responsible:id,name,email', 'status:id,name,color', 'priority:id,name,color', 'type:id,name', 'project:id,name,ticket_prefix', 'epic:id,name', 'sprint:id,name']);

        $includeComments = $args['include_comments'] ?? true;
        if ($includeComments) {
            $ticket->load(['comments' => fn ($q) => $q->with('user:id,name,email')->orderBy('created_at')]);
        }

        $data = [
            'id' => $ticket->id,
            'code' => $ticket->code,
            'name' => $ticket->name,
            'content' => $ticket->content,
            'status' => $ticket->status ? ['id' => $ticket->status->id, 'name' => $ticket->status->name, 'color' => $ticket->status->color] : null,
            'priority' => $ticket->priority ? ['id' => $ticket->priority->id, 'name' => $ticket->priority->name, 'color' => $ticket->priority->color] : null,
            'type' => $ticket->type ? ['id' => $ticket->type->id, 'name' => $ticket->type->name] : null,
            'project' => $ticket->project ? ['id' => $ticket->project->id, 'name' => $ticket->project->name] : null,
            'epic' => $ticket->epic ? ['id' => $ticket->epic->id, 'name' => $ticket->epic->name] : null,
            'sprint' => $ticket->sprint ? ['id' => $ticket->sprint->id, 'name' => $ticket->sprint->name] : null,
            'owner' => $ticket->owner ? ['id' => $ticket->owner->id, 'name' => $ticket->owner->name, 'email' => $ticket->owner->email] : null,
            'responsible' => $ticket->responsible ? ['id' => $ticket->responsible->id, 'name' => $ticket->responsible->name, 'email' => $ticket->responsible->email] : null,
            'due_date' => optional($ticket->due_date)->toIso8601String(),
            'created_at' => optional($ticket->created_at)->toIso8601String(),
            'updated_at' => optional($ticket->updated_at)->toIso8601String(),
        ];

        if ($includeComments) {
            $data['comments_count'] = $ticket->comments->count();
            $data['comments'] = $ticket->comments->map(fn ($c) => [
                'id' => $c->id,
                'author' => $c->user ? ['id' => $c->user->id, 'name' => $c->user->name, 'email' => $c->user->email] : null,
                'content' => $c->content,
                'created_at' => optional($c->created_at)->toIso8601String(),
            ])->values();
        }

        return $data;
    }
}
