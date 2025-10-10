<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\ValesComida;
use Illuminate\Validation\ValidationException;

class CrearValeComida extends Component
{
    public $search = '';
    public $selectedUserId = '';
    public $selectedUserName = '';
    public $fecha = '';
    public $monto = '';
    public $num_elementos = '';

    public $usuarios = [];

    public function updatedSearch()
    {
        if (strlen($this->search) > 2) {
            $this->usuarios = User::where('estatus', 'Activo')
                ->where('name', 'like', '%' . $this->search . '%')
                ->limit(10)
                ->get();
        } else {
            $this->usuarios = [];
        }
    }

    public function selectUser($userId, $userName)
    {
        $this->selectedUserId = $userId;
        $this->selectedUserName = $userName;
        $this->search = $userName;
        $this->usuarios = [];
    }

    public function save()
    {
        $this->validate([
            'selectedUserId' => 'required|exists:users,id',
            'fecha' => 'required|date',
            'monto' => 'required|numeric|min:0.01',
            'num_elementos' => 'required|integer|min:1',
        ], [
            'selectedUserId.required' => 'Debe seleccionar un usuario',
            'fecha.required' => 'La fecha es requerida',
            'monto.required' => 'El monto es requerido',
            'monto.min' => 'El monto debe ser mayor a 0',
            'num_elementos.required' => 'El número de elementos es requerido',
            'num_elementos.min' => 'Debe ser al menos 1',
        ]);

        ValesComida::create([
            'user_id' => $this->selectedUserId,
            'fecha' => $this->fecha,
            'monto' => $this->monto,
            'num_elementos' => $this->num_elementos,
            'estatus' => 'En Proceso',
        ]);

        session()->flash('success', 'Vale de comida creado exitosamente');

        $this->reset(['search', 'selectedUserId', 'selectedUserName', 'fecha', 'monto', 'num_elementos']);
    }

    public function render()
    {
        return view('livewire.crear-vale-comida');
    }
}
