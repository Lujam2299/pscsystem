<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Usaremos ShouldBroadcast
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MensajeEnviado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->message->created_at = \Carbon\Carbon::parse($this->message->created_at)->tz('America/Mexico_City');
    }

    public function broadcastOn()
    {
        return new PresenceChannel('conversacion.' . $this->message->conversation_id);
    }

    public function broadcastAs()
    {
        return 'MensajeEnviado';
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message->load('user')->toArray(),
            'conversation_id' => $this->message->conversation_id,
        ];
    }
}
