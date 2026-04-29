<?php

namespace App\Livewire;

use App\Models\SolicitudVacaciones;
use App\Models\User;
use Livewire\Component;

class KardexVacaciones extends Component
{
    public $search = '';
    public $selectedUser = null;
    public $showDropdown = false;
    public $vacaciones = [];

    protected $listeners = ['userSelected' => 'loadUserKardex'];

    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->showDropdown = true;
        } else {
            $this->showDropdown = false;
        }
    }

    public function selectUser($userId)
    {
        $this->selectedUser = User::find($userId);
        $this->search = $this->selectedUser ? $this->selectedUser->name : '';
        $this->showDropdown = false;
        $this->loadUserKardex();
    }

    public function loadUserKardex()
    {
        if ($this->selectedUser) {
            $this->vacaciones = SolicitudVacaciones::where('user_id', $this->selectedUser->id)
                ->where('estatus', 'Aceptada')
                ->orderBy('periodo', 'asc')
                ->orderBy('fecha_inicio', 'asc')
                ->get();
        } else {
            $this->vacaciones = [];
        }
    }

    public function clearSelection()
    {
        $this->selectedUser = null;
        $this->search = '';
        $this->vacaciones = [];
        $this->showDropdown = false;
    }

    public function render()
    {
        $users = [];

        if (strlen($this->search) >= 2) {
            $users = User::where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->limit(10)
                ->get();
        }

        return view('livewire.kardex-vacaciones', [
            'users' => $users
        ]);
    }
}
