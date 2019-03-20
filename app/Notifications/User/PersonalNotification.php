<?php

namespace App\Notifications\User;

use App\Notifications\StaffDatabaseChannel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PersonalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;

    /**
     * Create a new notification instance.
     *
     * @param array $message
     */
    public function __construct(array $message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return [StaffDatabaseChannel::class, 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        $array = [
            'id'         => $this->id,
            'title'      => $this->message['title'],
            'message'    => $this->message['message'],
            'style'      => $this->message['type'] ?? 'success',
            'sound'      => $this->message['sound'] ?? true,
            'from'       => ['id' => auth()->user()->id, 'name' => auth()->user()->first_name],
            'link'       => $this->message['link'] ?? '#',
            'time'       => Carbon::now()->format('d.m.Y H:i:s'),
            'identifier' => $this->message['identifier'] ?? '',
        ];

        if (isset($this->message['displayParams'])) {
            $array['displayParams'] = $this->message['displayParams'];
        }

        return $array;
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
