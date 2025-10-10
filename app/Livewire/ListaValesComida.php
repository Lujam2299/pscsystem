<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ValesComida;

class ListaValesComida extends Component
{
    use WithPagination;

    public $search = '';
    public $fecha = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFecha() { $this->resetPage(); }

    public function render()
    {
        $query = ValesComida::with('user')
            ->orderBy('fecha', 'desc');

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->fecha) {
            $query->whereDate('fecha', $this->fecha);
        }

        $vales = $query->paginate(10);

        return view('livewire.lista-vales-comida', [
            'vales' => $vales,
        ]);
    }
}
