<?php

namespace App\Events\Messenger;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessengerMessageRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conversationId;
    public int $messageId;
    public int $readerId;
    public string $readAt;

    public function __construct(int $conversationId, int $messageId, int $readerId, string $readAt)
    {
        $this->conversationId = $conversationId;
        $this->messageId = $messageId;
        $this->readerId = $readerId;
        $this->readAt = $readAt;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('messenger.conversation.' . $this->conversationId);
    }

    public function broadcastAs(): string
    {
        return 'message.read';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'reader_id' => $this->readerId,
            'read_at' => $this->readAt,
        ];
    }
}
