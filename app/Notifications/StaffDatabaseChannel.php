<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class StaffDatabaseChannel
{
    public function send($notifiable, Notification $notification)
    {
        $data = method_exists($notification, 'toDatabase') ? $notification->toDatabase($notifiable) : $notification->toArray($notifiable);

        return $notifiable->routeNotificationFor('database')->create([
            'id' => $notification->id,

            'staff_id'=> auth()->check() ? auth()->user()->id : null,

            'type' => get_class($notification),
            'data' => $data,
            'read_at' => null,
        ]);
    }
}