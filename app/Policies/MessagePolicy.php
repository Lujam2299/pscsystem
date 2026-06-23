<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function delete(User $user, Message $message): bool
    {
        return (int) $message->user_id === (int) $user->id
            && $message->created_at?->greaterThanOrEqualTo(now()->subMinutes(15));
    }
}
