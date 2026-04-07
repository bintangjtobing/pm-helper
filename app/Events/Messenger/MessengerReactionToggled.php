<?php

namespace App\Events\Messenger;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessengerReactionToggled implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conversationId;
    public int $messageId;
    public int $userId;
    public string $emoji;
    public string $action; // 'added' | 'removed' | 'changed'

    public function __construct(int $conversationId, int $messageId, int $userId, string $emoji, string $action)
    {
        $this->conversationId = $conversationId;
        $this->messageId = $messageId;
        $this->userId = $userId;
        $this->emoji = $emoji;
        $this->action = $action;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('messenger.conversation.' . $this->conversationId);
    }

    public function broadcastAs(): string
    {
        return 'reaction.toggled';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'user_id' => $this->userId,
            'emoji' => $this->emoji,
            'action' => $this->action,
        ];
    }
}
