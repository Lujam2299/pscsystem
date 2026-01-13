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

    public function updatedConversationId($value)
    {
        \Log::info('conversationId actualizado:', ['conversationId' => $value]);
    }

    public function mount()
    {
        $this->componentId = 'chat-' . uniqid();
    }

    public function agregarMensaje($data)
    {
        \Log::info('Evento mensajeRecibido recibido en MensajesChat:', [
            'data' => $data,
            'conversation_id' => $this->conversation ? $this->conversation->id : null,
        ]);

        $messageData = $data['message'] ?? $data;
        $conversationId = $data['conversation_id'] ?? $messageData['conversation_id'] ?? null;

        // Verificar que no sea el mismo usuario que envió el mensaje (para evitar duplicados)
        $senderUserId = $messageData['user_id'] ?? $messageData['user']['id'] ?? null;
        $isOwnMessage = $senderUserId == Auth::id();

        if ($this->conversation && $this->conversation->id == $conversationId && !$isOwnMessage) {
            \Log::info('Añadiendo mensaje a la conversación:', [
                'message' => $messageData,
            ]);

            $this->messages[] = $messageData;
            $this->dispatch('scrollToBottom');
        } else if ($isOwnMessage) {
            \Log::debug('Mensaje propio ignorado para evitar duplicado');
        }
    }

    public function cargarConversacion($id)
    {
        \Log::info('Cargando conversación:', ['conversation_id' => $id]);
        if (is_null($id)) {
            $this->conversation = null;
            $this->messages = [];
            $this->conversationId = null;
            \Log::warning('Conversación ID nula');
            return;
        }

        $this->conversationId = $id;
        $this->dispatch('updatedConversationId', $id);
        $this->conversation = Conversation::with(['messages.user', 'users'])->find($id);

        if ($this->conversation) {
            $this->messages = $this->conversation->messages->toArray();
            \Log::info('Conversación cargada:', ['conversation_id' => $id]);
            $this->dispatch('refreshComponent');
        } else {
            $this->messages = [];
            $this->conversationId = null;
            \Log::warning('Conversación no encontrada:', ['conversation_id' => $id]);
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

        \Log::info('Mensaje enviado:', [
            'message_id' => $msg->id,
            'conversation_id' => $this->conversation->id,
            'user_id' => Auth::id()
        ]);

        try {
            broadcast(new MensajeEnviado($msg))->toOthers();
            \Log::info('Evento MensajeEnviado emitido exitosamente:', [
                'message_id' => $msg->id,
                'conversation_id' => $this->conversation->id
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al emitir MensajeEnviado:', [
                'message_id' => $msg->id,
                'conversation_id' => $this->conversation->id,
                'error' => $e->getMessage()
            ]);
        }

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
