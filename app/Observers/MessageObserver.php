<?php

namespace App\Observers;

use App\Events\ConversationUpdated;
use App\Models\Message;
use App\Services\RealtimeToast;
use Illuminate\Support\Str;

class MessageObserver
{
    public function created(Message $message): void
    {
        $message->loadMissing(['user', 'conversation.users']);

        RealtimeToast::toUsers(
            $message->conversation?->users?->pluck('id') ?? [],
            [
                'icon' => 'info',
                'title' => 'Nuevo mensaje de '.($message->user?->name ?? 'un participante'),
                'text' => Str::limit($message->body, 120),
                'url' => route('mensajes.index'),
                // 'url' => route('mensajes.show', $message->conversation_id),
                'key' => 'message:'.$message->id,
            ],
            (int) $message->user_id,
        );

        $recipientIds = $message->conversation->users->pluck('id')->map(fn ($id) => (int) $id)->all();
        broadcast(new ConversationUpdated($message, $recipientIds))->toOthers();
    }
}
