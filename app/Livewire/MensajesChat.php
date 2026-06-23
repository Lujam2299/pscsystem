<?php

namespace App\Livewire;

use App\Events\MensajeEnviado;
use App\Events\MessagesRead;
use App\Events\MessageDeleted;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class MensajesChat extends Component
{
    public ?Conversation $conversation = null;
    public array $messages = [];
    public string $body = '';
    public string $componentId;
    public ?int $conversationId = null;
    public ?int $replyToMessageId = null;
    public string $buscarMensaje = '';
    public bool $hasMoreMessages = false;
    public string $sendState = 'idle';

    protected $listeners = [
        'conversacionSeleccionada' => 'cargarConversacion',
        'cerrarConversacion' => 'cerrarConversacion',
    ];

    public function mount(): void
    {
        $this->componentId = 'chat-' . uniqid();
    }

    public function cargarConversacion($id): void
    {
        if (!$id) {
            $this->cerrarConversacion();
            return;
        }

        $conversation = Conversation::with('users')->findOrFail($id);
        Gate::authorize('view', $conversation);

        $this->conversation = $conversation;
        $this->conversationId = (int) $conversation->id;
        $this->replyToMessageId = null;
        $this->buscarMensaje = '';
        $this->cargarMensajesRecientes();
        $this->marcarComoLeidos();
        $this->dispatch('updatedConversationId', conversationId: $this->conversationId);
        $this->dispatch('scrollToBottom');
    }

    private function consultaMensajes()
    {
        return Message::query()
            ->where('conversation_id', $this->conversationId)
            ->with(['user', 'parent.user']);
    }

    private function cargarMensajesRecientes(): void
    {
        $items = $this->consultaMensajes()->latest('id')->limit(31)->get();
        $this->hasMoreMessages = $items->count() > 30;
        $this->messages = $items->take(30)->reverse()->values()->toArray();
    }

    public function cargarAnteriores(): void
    {
        if (!$this->conversationId || !$this->messages || !$this->hasMoreMessages) return;

        $oldestId = collect($this->messages)->min('id');
        $items = $this->consultaMensajes()->where('id', '<', $oldestId)->latest('id')->limit(31)->get();
        $this->hasMoreMessages = $items->count() > 30;
        $older = $items->take(30)->reverse()->values()->toArray();
        $this->messages = array_merge($older, $this->messages);
        $this->dispatch('preserveScrollPosition');
    }

    public function updatedBuscarMensaje(): void
    {
        if (!$this->conversationId) return;

        if (mb_strlen(trim($this->buscarMensaje)) < 2) {
            $this->cargarMensajesRecientes();
            return;
        }

        $this->messages = $this->consultaMensajes()
            ->where('body', 'like', '%' . trim($this->buscarMensaje) . '%')
            ->latest('id')->limit(50)->get()->reverse()->values()->toArray();
        $this->hasMoreMessages = false;
    }

    public function enviarMensaje(): void
    {
        $this->validate(['body' => 'required|string|max:1000']);
        abort_unless($this->conversationId, 422);
        $conversation = Conversation::findOrFail($this->conversationId);
        Gate::authorize('view', $conversation);
        $this->sendState = 'sending';

        try {
            $msg = $conversation->messages()->create([
                'user_id' => Auth::id(),
                'body' => trim($this->body),
                'parent_id' => $this->replyToMessageId,
            ])->load(['user', 'parent.user']);

            $this->messages[] = $msg->toArray();
            $this->reset(['body', 'replyToMessageId']);
            broadcast(new MensajeEnviado($msg))->toOthers();
            $this->sendState = 'sent';
            $this->dispatch('mensajeEnviadoEnConversacion', conversation_id: $conversation->id);
            $this->dispatch('scrollToBottom');
        } catch (\Throwable $exception) {
            report($exception);
            $this->sendState = 'error';
        }
    }

    public function agregarMensaje($data): void
    {
        $messageData = $data['message'] ?? $data;
        if ((int) ($messageData['conversation_id'] ?? 0) !== $this->conversationId) return;
        if ((int) ($messageData['user_id'] ?? 0) === (int) Auth::id()) return;
        if (collect($this->messages)->contains('id', $messageData['id'] ?? null)) return;

        $this->messages[] = $messageData;
        $this->marcarComoLeidos();
        $this->dispatch('scrollToBottomIfNearEnd');
    }

    public function actualizarLecturas(): void
    {
        if (!$this->conversationId) return;
        $readIds = Message::where('conversation_id', $this->conversationId)
            ->where('user_id', Auth::id())->whereNotNull('read_at')->pluck('id');
        $this->messages = collect($this->messages)->map(function ($message) use ($readIds) {
            if ($readIds->contains($message['id'])) $message['read_at'] = now()->toIso8601String();
            return $message;
        })->all();
    }

    public function responderA(int $messageId): void
    {
        $message = $this->consultaMensajes()->findOrFail($messageId);
        $this->replyToMessageId = $message->id;
        $this->dispatch('focusMessageInput');
    }

    public function cancelarRespuesta(): void
    {
        $this->replyToMessageId = null;
    }

    public function eliminarMensaje(int $messageId): void
    {
        $message = $this->consultaMensajes()->findOrFail($messageId);
        Gate::authorize('delete', $message);
        $message->delete();
        $this->messages = collect($this->messages)->reject(fn ($item) => (int) $item['id'] === $messageId)->values()->all();
        broadcast(new MessageDeleted((int) $this->conversationId, $messageId))->toOthers();
        $this->dispatch('mensajeEnviadoEnConversacion', conversation_id: $this->conversationId);
    }

    public function quitarMensajeEliminado(int $messageId): void
    {
        $this->messages = collect($this->messages)->reject(fn ($item) => (int) $item['id'] === $messageId)->values()->all();
    }

    private function marcarComoLeidos(): void
    {
        Message::where('conversation_id', $this->conversationId)
            ->where('user_id', '!=', Auth::id())->whereNull('read_at')->update(['read_at' => now()]);

        DB::table('conversation_user')->where('conversation_id', $this->conversationId)
            ->where('api_user_id', Auth::id())->update(['unread_count' => 0, 'last_read_at' => now()]);
        $this->dispatch('mensajesLeidos', conversation_id: $this->conversationId);
        broadcast(new MessagesRead((int) $this->conversationId, (int) Auth::id()))->toOthers();
    }

    public function cerrarConversacion(): void
    {
        $this->reset(['conversation', 'messages', 'conversationId', 'replyToMessageId', 'buscarMensaje']);
        $this->dispatch('updatedConversationId', conversationId: null);
        $this->dispatch('cerrar-chat-movil');
    }

    public function render()
    {
        return view('livewire.mensajes-chat');
    }
}
