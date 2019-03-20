<?php

namespace App\Events\McMessage;

use App\Events\AmqpEvent;
use App\Models\McMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;

class MessageChange extends AmqpEvent
{
    use InteractsWithSockets;

    /**
     * @var McMessage
     */
    public $message;

    /**
     * Create a new event instance.
     *
     * @param McMessage $message
     */
    public function __construct(McMessage $message)
    {
        $this->message = $message;
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        // Наименование канала доставки:
        $name = sprintf('mc-chat-conversation.%d', $this->message->conversation_id);

        return new Channel($name);
    }
}
