<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.Staff.{id}', function ($user, $id) {
    if((int) $user->id === (int) $id)
        return ['id' => $user->id, 'name' => $user->name];
});

Broadcast::channel('notification.{userId}', function ($user, $userId) {
    if(auth()->check() && (int) $user->id === (int) $userId) return ['id' => $user->id, 'name' => $user->name];
});

Broadcast::channel('dealer-table.{id}', function ($user, $id) {
        return ['id' => $user->id, 'name' => $user->name];
});

Broadcast::channel('notification', function ($user) {
    if(auth()->check())
        return ['id' => $user->id, 'name' => $user->name];
});