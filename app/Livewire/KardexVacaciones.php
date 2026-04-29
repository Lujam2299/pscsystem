<?php

namespace App\Livewire;

use App\Models\SolicitudVacaciones;
use App\Models\User;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

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

    /**
     * Exportar kárdex de vacaciones a PDF
     */
    public function exportToPdf()
    {
        if (!$this->selectedUser) {
            session()->flash('error', 'Selecciona un usuario primero.');
            return;
        }

        $user = $this->selectedUser;
        $vacaciones = $this->vacaciones;

        // Calcular resumen por periodo
        $resumenPorPeriodo = $vacaciones->groupBy('periodo')->map(function($items, $periodo) {
            return [
                'periodo' => $periodo,
                'dias_por_derecho' => $items->first()->dias_por_derecho,
                'dias_disponibles' => $items->first()->dias_disponibles,
                'dias_solicitados' => $items->sum('dias_solicitados'),
                'dias_restantes' => $items->first()->dias_disponibles - $items->sum('dias_solicitados'),
            ];
        });

        $fotoRuta = $user->documentacionAltas?->arch_foto;

        $pdf = Pdf::loadView('pdf.kardex-vacaciones', [
            'user' => $user,
            'vacaciones' => $vacaciones,
            'resumenPorPeriodo' => $resumenPorPeriodo,
            'fotoRuta' => $fotoRuta,
            'fechaGeneracion' => now()->format('d/m/Y H:i:s')
        ]);

        $pdf->setPaper('letter', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "kardex_vacaciones_{$user->name}_{$user->id}_" . date('Y-m-d') . ".pdf"
        );
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
