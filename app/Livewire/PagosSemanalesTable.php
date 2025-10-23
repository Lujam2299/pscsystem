<?php

namespace App\Livewire;

use App\Models\ArchivoPagoSemanal;
use Livewire\Component;
use Livewire\WithPagination;

class PagosSemanalesTable extends Component
{
    use WithPagination;

    public $search = '';
    public $anio = '';
    public $mes = '';

    public $orden = 'created_at';
    public $direccion = 'desc';

    protected $queryString = ['search', 'anio', 'mes', 'orden', 'direccion'];

    public function getAniosProperty()
    {
        return ArchivoPagoSemanal::select('anio')
            ->distinct()
            ->orderBy('anio', 'desc')
            ->pluck('anio')
            ->toArray();
    }

    public function ordenarPor($campo)
    {
        if ($this->orden === $campo) {
            $this->direccion = $this->direccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->orden = $campo;
            $this->direccion = 'asc';
        }
    }

    public function render()
    {
        $query = ArchivoPagoSemanal::query();

        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('anio', 'like', $search)
                  ->orWhere('semana', 'like', $search);
            });
        }

        if (!empty($this->anio)) {
            $query->where('anio', $this->anio);
        }

        if (!empty($this->mes)) {
            $mesesTexto = [
                'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
            ];
            $mesNumero = array_search(strtolower($this->mes), $mesesTexto);
            if ($mesNumero !== false) {
                $query->where('mes', $mesNumero + 1);
            }
        }

        $query->orderBy($this->orden, $this->direccion);

        $registros = $query->paginate(10);

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        return view('livewire.pagos-semanales-table', compact('registros', 'meses'));
    }
}
