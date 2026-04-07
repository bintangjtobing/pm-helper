<?php

namespace App\Events\Messenger;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessengerStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public ?string $status;
    public ?string $statusMessage;
    public ?string $onLeaveUntil;

    public function __construct(User $user)
    {
        $this->userId = (int) $user->id;
        $this->status = $user->effectiveStatus();
        $this->statusMessage = $user->status_message;
        $this->onLeaveUntil = $user->on_leave_until?->toIso8601String();
    }

    public function broadcastOn(): Channel
    {
        // Re-use the messenger online presence channel — every connected
        // user is already subscribed there, so a single broadcast reaches
        // all clients that need to update the status indicator.
        return new PresenceChannel('messenger.online');
    }

    public function broadcastAs(): string
    {
        return 'status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'status' => $this->status,
            'status_message' => $this->statusMessage,
            'on_leave_until' => $this->onLeaveUntil,
        ];
    }
}
