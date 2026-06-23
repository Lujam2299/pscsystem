<?php

use Illuminate\Support\Facades\Broadcast;
Broadcast::channel('conversacion.{id}', function ($user, $id) {
    return $user->conversations()->whereKey($id)->exists()
        ? ['id' => $user->id, 'name' => $user->name]
        : false;
});
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

