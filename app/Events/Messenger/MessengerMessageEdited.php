<?php

namespace App\Events\Messenger;

use App\Models\MessengerMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessengerMessageEdited implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conversationId;
    public int $messageId;
    public string $body;
    public string $editedAt;

    public function __construct(MessengerMessage $message)
    {
        $this->conversationId = (int) $message->conversation_id;
        $this->messageId = (int) $message->id;
        $this->body = (string) $message->body;
        $this->editedAt = optional($message->edited_at)->toIso8601String() ?? now()->toIso8601String();
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('messenger.conversation.' . $this->conversationId);
    }

    public function broadcastAs(): string
    {
        return 'message.edited';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'body' => $this->body,
            'edited_at' => $this->editedAt,
        ];
    }
}
