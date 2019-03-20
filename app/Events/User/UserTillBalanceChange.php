<?php

namespace App\Events\User;

use App\Events\AmqpEvent;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;

use App\Models\UserTill;

class UserTillBalanceChange extends AmqpEvent
{
    use InteractsWithSockets;

    /**
     * @var UserTill
     */
    public $userTill;

    /**
     * Create a new event instance.
     *
     * @param UserTill $userTill
     */
    public function __construct(UserTill $userTill)
    {
        $this->userTill = $userTill;
    }

    public function broadcastWith()
    {
        // Содержимое тела сообщения.
        // Сюда лучше "не пихать ничего лишнего", чтобы не засветиться.
        return [
            'user_id' => $this->userTill->user_id,
            'balance' => $this->userTill->balance
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn()
    {
        // Наименование канала доставки:
        $name = sprintf('user.%d', $this->userTill->user_id);

        return new PrivateChannel($name);
    }
}
