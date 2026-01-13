<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // Usaremos ShouldBroadcast
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MensajeEnviado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        \Log::info('Canal de broadcast:', ['channel' => 'conversacion.' . $this->message->conversation_id]);
        return new PrivateChannel('conversacion.' . $this->message->conversation_id);
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
