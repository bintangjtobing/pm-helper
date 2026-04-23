<?php

namespace App\Mcp\Tools;

use App\Mcp\Tool;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;

class UpdateTicketStatusTool implements Tool
{
    public function name(): string
    {
        return 'update_ticket_status';
    }

    public function description(): string
    {
        return 'Move one ticket to a new status. Requires the user to have update permission on the ticket and the target status to be settable by the user (respects role-group gates).';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'code' => ['type' => 'string', 'description' => 'Ticket code (e.g. "wd-51"). Alternative to id.'],
                'status_id' => ['type' => 'integer'],
                'status_name' => ['type' => 'string', 'description' => 'Status name, case-insensitive. Alternative to status_id.'],
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
        if (! $user->can('update', $ticket)) {
            throw new \RuntimeException('Access denied to update this ticket.');
        }

        $target = null;
        if (! empty($args['status_id'])) {
            $target = TicketStatus::find((int) $args['status_id']);
        } elseif (! empty($args['status_name'])) {
            $target = TicketStatus::whereRaw('LOWER(name) = ?', [strtolower(trim((string) $args['status_name']))])->first();
        }
        if (! $target) {
            throw new \RuntimeException('Status not found.');
        }

        if (! $target->canBeSetByUser()) {
            throw new \RuntimeException("You cannot move tickets to status '{$target->name}'.");
        }

        $ticket->status_id = $target->id;
        $ticket->save();

        return [
            'id' => $ticket->id,
            'code' => $ticket->code,
            'status' => ['id' => $target->id, 'name' => $target->name],
            'message' => 'Ticket status updated.',
        ];
    }
}
