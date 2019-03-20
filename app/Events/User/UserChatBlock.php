<?php

namespace App\Events\User;

use App\Events\AmqpEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;

class UserChatBlock extends AmqpEvent
{
    use InteractsWithSockets;

    /**
     * @var User
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function broadcastWith()
    {
        // Содержимое тела сообщения.
        // Сюда лучше "не пихать ничего лишнего", чтобы не засветиться.
        return [
            'user_id'            => $this->user->id,
            'blocked_chat_until' => $this->user->blocked_chat_until ? $this->user->blocked_chat_until->format(config('selectOptions.common.dateTime')) : null,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn()
    {
        $name = sprintf('user.%d', $this->user->id);

        return new PrivateChannel($name);
    }
}
