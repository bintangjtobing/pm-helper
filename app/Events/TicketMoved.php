<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketMoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $projectId;
    public int $ticketId;
    public ?int $oldStatusId;
    public int $newStatusId;
    public int $newIndex;
    public int $movedByUserId;

    public function __construct(
        int $projectId,
        int $ticketId,
        ?int $oldStatusId,
        int $newStatusId,
        int $newIndex,
        int $movedByUserId
    ) {
        $this->projectId = $projectId;
        $this->ticketId = $ticketId;
        $this->oldStatusId = $oldStatusId;
        $this->newStatusId = $newStatusId;
        $this->newIndex = $newIndex;
        $this->movedByUserId = $movedByUserId;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('project.' . $this->projectId . '.kanban');
    }

    public function broadcastAs(): string
    {
        return 'ticket.moved';
    }

    public function broadcastWith(): array
    {
        return [
            'project_id' => $this->projectId,
            'ticket_id' => $this->ticketId,
            'old_status_id' => $this->oldStatusId,
            'new_status_id' => $this->newStatusId,
            'new_index' => $this->newIndex,
            'moved_by_user_id' => $this->movedByUserId,
        ];
    }
}
