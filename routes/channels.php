<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('conversacion.{id}', function ($user, $id) {
    \Log::info('ERP - Autorización canal conversacion: ' . $id, [
        'user_id' => $user->id,
        'user_model' => get_class($user),
        'user_conversations' => $user->conversations->pluck('id')->toArray(),
        'target_conversation' => $id,
        'has_access' => $user->conversations->pluck('id')->contains($id)
    ]);

    return $user->conversations->pluck('id')->contains($id);
});
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


