<?php

namespace App\Events\Messenger;

use App\Models\MessengerMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessengerMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conversationId;
    public int $messageId;
    public int $senderId;

    public function __construct(MessengerMessage $message)
    {
        $this->conversationId = (int) $message->conversation_id;
        $this->messageId = (int) $message->id;
        $this->senderId = (int) $message->sender_id;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('messenger.conversation.' . $this->conversationId);
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'message_id' => $this->messageId,
            'sender_id' => $this->senderId,
        ];
    }
}
