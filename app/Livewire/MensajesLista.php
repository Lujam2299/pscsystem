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
    public $buscarConversacion = '';
    public $selectedConversationId = null;

    protected $listeners = [
        'forzarRender' => 'cargarConversaciones',
        'eliminarConversacionJS' => 'eliminarConversacion',
        'MensajeEnviado' => 'actualizarUltimoMensaje',
        'mensajeEnviadoEnConversacion' => 'actualizarConversacionEnLista',
        'conversacionSeleccionada' => 'marcarSeleccionada',
        'cerrarConversacion' => 'limpiarSeleccion',
    ];

    public function mount()
    {
        $this->cargarConversaciones();
    }

public function cargarConversaciones()
{
    $this->conversaciones = Auth::user()
        ->conversations()
        ->with([
            'users' => function($query) {
                $query->withPivot(['last_read_at', 'unread_count']);
            },
            'latestMessage'
        ])
        ->when(mb_strlen(trim($this->buscarConversacion)) >= 2, function ($query) {
            $term = trim($this->buscarConversacion);
            $query->where(function ($query) use ($term) {
                $query->where('title', 'like', "%{$term}%")
                    ->orWhereHas('users', fn ($users) => $users->where('users.name', 'like', "%{$term}%"));
            });
        })->orderByDesc('updated_at')
        ->get();
}

    public function updatedBuscarConversacion(): void
    {
        $this->cargarConversaciones();
    }

    public function actualizarUltimoMensaje($data = null)
    {
        $this->cargarConversaciones();
    }

    public function actualizarConversacionEnLista($conversation_id = null)
    {
        $this->cargarConversaciones();
    }

    public function marcarSeleccionada($id)
    {
        $this->selectedConversationId = $id;
        $this->cargarConversaciones();
    }

    public function limpiarSeleccion()
    {
        $this->selectedConversationId = null;
    }

    public function updatedBuscarUsuario($value)
    {
        if (strlen($value) >= 2) {
            $this->usuariosFiltrados = User::where('id', '!=', auth()->id())
                ->where('estatus', 'Activo')
                ->when(auth()->user()->rol !== 'admin', fn ($query) => $query->where('empresa', auth()->user()->empresa))
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
        $existe = Conversation::where('is_group', false)
            ->whereHas('users', fn($q) => $q->where('users.id', $usuarioId))
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
        $this->dispatch('conversacionSeleccionada', id: $conversationId);
        $this->dispatch('abrir-chat-movil');
    }

    public function eliminarConversacion($payload)
    {
        $id = $payload['id'];
        $conv = Conversation::with('messages')->find($id);

        if (!$conv || !$conv->users->pluck('id')->contains(auth()->id())) {
            return;
        }

        // Sin un estado por usuario no es seguro borrar historiales compartidos.
        abort_if($conv->messages()->exists(), 422, 'Solo se pueden eliminar conversaciones vacías.');

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
        return view('livewire.mensajes-lista');
    }
}
