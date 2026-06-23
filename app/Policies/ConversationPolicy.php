<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        if ($conversation->relationLoaded('users')) {
            return $conversation->users->contains(fn (User $participant) => (int) $participant->id === (int) $user->id);
        }

        return $conversation->users()->whereKey($user->id)->exists();
    }
}
