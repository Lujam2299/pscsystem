<?php

namespace App\Livewire;

use Livewire\Component;
use App\Events\MensajeEnviado;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

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
            // Forzar scroll al recibir mensaje
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
            // Forzar scroll al cargar conversación
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

        // Forzar scroll al enviar mensaje
        $this->dispatch('scrollToBottom');
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
