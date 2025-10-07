<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class HistorialAcusesAlta extends Component
{
    use WithPagination;

    public $search = '';
    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $usuarios = User::with('documentacionAltas')
            ->withTrashed()
            ->whereHas('documentacionAltas', function ($query) {
                $query->whereNotNull('arch_acuse_imss'); // Solo usuarios CON acuse subido
            })
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.historial-acuses-alta', [
            'usuarios' => $usuarios,
        ]);
    }
}
