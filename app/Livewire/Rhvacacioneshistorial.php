<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SolicitudVacaciones;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class Rhvacacioneshistorial extends Component
{
    use WithPagination;
    public $search = '';
    public $fecha = null;

    public $userId;
    protected $queryString = ['search' => ['except' => '']];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDate()
    {
        $this->resetPage();
    }


    public function render()
{
    $userId = Auth::user()->id;

    if (Auth::user()->rol == 'admin' || strtolower(Auth()->user()->rol) == 'auxiliar recursos humanos') {
        $solicitudes = SolicitudVacaciones::with('user')
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->fecha, function ($query) {
                $query->whereDate('fecha_inicio', $this->fecha);
            })
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(10);
    } else {
        $solicitudes = SolicitudVacaciones::with('user')
            ->where('supervisor_id', $userId)
            ->where(function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })->orWhere('fecha_inicio', 'like', '%' . $this->search . '%');
            })
            ->when($this->fecha, function ($query) {
                $query->whereDate('fecha_inicio', $this->fecha);
            })
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(10);
    }

    return view('livewire.rhvacacioneshistorial', compact('solicitudes'));
}
}
