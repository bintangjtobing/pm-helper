<?php

namespace App\Events\Messenger;

use App\Models\MessengerMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessengerMessageDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conversationId;
    public int $messageId;
    public bool $forEveryone;
    public int $actorId;

    public function __construct(MessengerMessage $message, bool $forEveryone, int $actorId)
    {
        $this->conversationId = (int) $message->conversation_id;
        $this->messageId = (int) $message->id;
        $this->forEveryone = $forEveryone;
        $this->actorId = $actorId;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('messenger.conversation.' . $this->conversationId);
    }

    public function broadcastAs(): string
    {
        return 'message.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'for_everyone' => $this->forEveryone,
            'actor_id' => $this->actorId,
        ];
    }
}
