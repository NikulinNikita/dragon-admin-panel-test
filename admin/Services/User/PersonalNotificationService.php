<?php

namespace Admin\Services\User;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class PersonalNotificationService
{
    /* @var User $user */
    public $user, $notification, $delay = null;

    /**
     *
     * @param Model $user
     * @param Notification $notification
     * @throws \Exception
     */
    public function __construct(Model $user, Notification $notification)
    {
        if(key_exists(Notifiable::class, class_uses($user))) {
            $this->user = $user;
        }
        else {
            throw new \Exception('Notifiable model should implement ' . Notifiable::class);
        }

        if(get_parent_class($notification) === Notification::class) {
            $this->notification = $notification;
        }
        else {
            throw new \Exception('Notification should extend ' . Notification::class);
        }
    }

    /**
     * @param Carbon $date
     * @return PersonalNotificationService
     * @throws \Exception
     */
    public function delay(Carbon $date) : PersonalNotificationService
    {
        if(key_exists(Queueable::class, class_uses($this->notification)) && key_exists(ShouldQueue::class, class_implements($this->notification))) {
            $this->delay = $date;

            $this->notification->delay($this->delay);
        }
        else {
            throw new \Exception('Notification should use '.Queueable::class. '.\n Notification should implement '.ShouldQueue::class);
        }

        return $this;
    }

    /**
     * @return void
     */
    public function send() : void {
        $this->user->notify($this->notification);
    }
}