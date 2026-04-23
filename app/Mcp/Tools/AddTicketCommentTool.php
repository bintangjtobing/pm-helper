<?php

namespace App\Mcp\Tools;

use App\Mcp\Tool;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;

class AddTicketCommentTool implements Tool
{
    public function name(): string
    {
        return 'add_ticket_comment';
    }

    public function description(): string
    {
        return 'Add a comment to a ticket (by id or code). Content may be markdown or HTML. Mentions like @username are parsed downstream by the TicketComment created event.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['content'],
            'properties' => [
                'id' => ['type' => 'integer'],
                'code' => ['type' => 'string', 'description' => 'Ticket code (e.g. "wd-51"). Alternative to id.'],
                'content' => ['type' => 'string'],
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

        $content = trim((string) ($args['content'] ?? ''));
        if ($content === '') {
            throw new \RuntimeException('content is required.');
        }

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'content' => $content,
        ]);

        return [
            'id' => $comment->id,
            'ticket' => ['id' => $ticket->id, 'code' => $ticket->code],
            'created_at' => optional($comment->created_at)->toIso8601String(),
            'message' => 'Comment added.',
        ];
    }
}
