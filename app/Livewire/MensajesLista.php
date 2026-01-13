<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

class MensajesLista extends Component
{
    public $conversaciones;
    public $buscarUsuario = '';
    public $usuariosFiltrados = [];
    public $mostrarBuscador = false;
    public $enablePolling = true;

    protected $listeners = [
        'forzarRender' => '$refresh',
        'eliminarConversacionJS' => 'eliminarConversacion',
        'MensajeEnviado' => 'actualizarUltimoMensaje',
        'mensajeEnviadoEnConversacion' => 'actualizarConversacionEnLista',
        'conversacionSeleccionada' => 'detenerPolling',
        'cerrarConversacion' => 'iniciarPolling',
    ];

    public function mount()
    {
        $this->cargarConversaciones();
    }

    public function cargarConversaciones()
    {
        $this->conversaciones = Auth::user()
            ->conversations()
            ->with(['users.documentacionAltas', 'latestMessage'])
            ->orderByDesc('updated_at') // Ordenar por última actividad
            ->get();
    }

    public function actualizarUltimoMensaje($data)
    {
        $this->cargarConversaciones();
    }

    public function actualizarConversacionEnLista($data)
    {
        $this->cargarConversaciones();
    }

    public function detenerPolling($id)
    {
        $this->enablePolling = false;
    }

    public function iniciarPolling()
    {
        $this->enablePolling = true;
    }

    public function updatedBuscarUsuario($value)
    {
        if (strlen($value) >= 2) {
            $this->usuariosFiltrados = User::where('id', '!=', auth()->id())
                ->where('name', 'like', '%'.$value.'%')
                ->with('documentacionAltas')
                ->take(5)
                ->get();

            $this->dispatch('resultadosActualizados');
        } else {
            $this->usuariosFiltrados = [];
        }
    }

    public function iniciarConversacion($usuarioId)
    {
        $existe = Conversation::whereHas('users', fn($q) => $q->where('users.id', $usuarioId))
            ->whereHas('users', fn($q) => $q->where('users.id', auth()->id()))
            ->first();

        $conv = $existe ?: Conversation::create();
        if (!$existe) {
            $conv->users()->attach([
                $usuarioId => ['api_user_id' => $usuarioId],
                auth()->id() => ['api_user_id' => auth()->id()],
            ]);
        }

        $this->reset(['buscarUsuario', 'usuariosFiltrados', 'mostrarBuscador']);
        $this->dispatch('conversacionSeleccionada', id: $conv->id);
        $this->cargarConversaciones();
    }

    public function seleccionarConversacion($conversationId)
    {
        \Log::info('Seleccionando conversación:', ['conversation_id' => $conversationId]);
        $this->dispatch('conversacionSeleccionada', id: $conversationId);
    }

    public function eliminarConversacion($payload)
    {
        $id = $payload['id'];
        $conv = Conversation::with('messages')->find($id);

        if (!$conv || !$conv->users->pluck('id')->contains(auth()->id())) {
            return;
        }

        $conv->messages()->delete();
        $conv->users()->detach();
        $conv->delete();

        $this->cargarConversaciones();
        $this->dispatch('conversacionSeleccionada', id: null);
        $this->dispatch('conversacionEliminada');
    }

    public function confirmarEliminacion($conversationId)
    {
        $this->dispatch('confirmarEliminacionJS', $conversationId);
    }

    public function toggleBuscador()
    {
        $this->mostrarBuscador = !$this->mostrarBuscador;
        $this->dispatch('focusSearchInput');
    }

    public function render()
    {
        if ($this->enablePolling) {
            $this->cargarConversaciones();
        }

        return view('livewire.mensajes-lista');
    }
}
