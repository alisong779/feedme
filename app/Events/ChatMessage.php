<?php

namespace App\Events;

use App\Models\ChatMessage as ChatMessageModel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatMessageModel $message
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $user1 = min(
            $this->message->sender_id,
            $this->message->receiver_id
        );

        $user2 = max(
            $this->message->sender_id,
            $this->message->receiver_id
        );

        return [
            new PrivateChannel("chat.{$user1}.{$user2}"),
        ];
    }
}