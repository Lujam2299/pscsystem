<?php

namespace App\Livewire;

use Livewire\Component;
use App\Events\MensajeEnviado;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MensajesChat extends Component
{
    public $conversation;
    public $messages = [];
    public $body = '';
    public $componentId;
    public $conversationId;

    protected $listeners = [
        'conversacionSeleccionada' => 'cargarConversacion',
        'cerrarConversacion' => 'cerrarConversacion',
    ];

    public function mount()
    {
        $this->componentId = 'chat-' . uniqid();
    }

    public function agregarMensaje($data)
    {
        $messageData = $data['message'] ?? $data;
        $senderUserId = $messageData['user_id'] ?? $messageData['user']['id'] ?? null;
        $isOwnMessage = $senderUserId == Auth::id();

        if ($this->conversation && !$isOwnMessage) {
            $this->messages[] = $messageData;
            $this->dispatch('scrollToBottom');
        }
    }

    public function cargarConversacion($id)
{
    if (is_null($id)) {
        $this->conversation = null;
        $this->messages = [];
        $this->conversationId = null;
        return;
    }

    $this->conversationId = $id;
    $this->dispatch('updatedConversationId', $id);
    $this->conversation = Conversation::with(['messages.user', 'users'])->find($id);

    if ($this->conversation) {
        $this->messages = $this->conversation->messages->toArray();

        // Marcar mensajes como leídos para este usuario
        DB::table('conversation_user')
            ->where('conversation_id', $id)
            ->where('api_user_id', auth()->id()) // Cambié a api_user_id
            ->update(['unread_count' => 0]);

        $this->dispatch('scrollToBottom');
    } else {
        $this->messages = [];
        $this->conversationId = null;
    }
}

    public function enviarMensaje()
    {
        $this->validate(['body' => 'required|string']);

        $msg = $this->conversation->messages()->create([
            'user_id' => Auth::id(),
            'body' => $this->body,
        ]);

        $msg->load('user');
        $this->messages[] = $msg->toArray();
        $this->body = '';

        try {
            broadcast(new MensajeEnviado($msg))->toOthers();
        } catch (\Exception $e) {
            // Log opcional
        }

        $this->dispatch('scrollToBottom');

        // Notificar a mensajes-lista que hay un nuevo mensaje
        $this->dispatch('mensajeEnviadoEnConversacion', [
            'conversation_id' => $this->conversation->id,
            'message_body' => $msg->body,
            'user_id' => $msg->user_id,
        ]);
    }

    public function cerrarConversacion()
    {
        $this->conversation = null;
        $this->messages = [];
    }

    public function render()
    {
        return view('livewire.mensajes-chat');
    }
}
