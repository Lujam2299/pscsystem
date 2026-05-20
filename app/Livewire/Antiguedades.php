<?php

namespace App\Livewire;

use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;

class Antiguedades extends Component
{
    use WithPagination;

    public $filtroQuincena = 'todas';
    public $filtroMes = 'todos';
    public $filtroAnio = 'todos';

    public function mount()
    {
        $hoy = Carbon::now();
        $this->filtroMes = $this->filtroMes === 'todos' ? $hoy->month : $this->filtroMes;
        $this->filtroQuincena = $this->filtroQuincena === 'todas' ? ($hoy->day <= 15 ? '1' : '2') : $this->filtroQuincena;
    }

    public function render()
    {
        // ✅ Paginación nativa: todo en BD, simple y directo
        $usuarios = User::query()
            ->with(['solicitudAlta', 'documentacionAltas'])
            ->where('users.estatus', 'Activo')
            ->when($this->filtroMes !== 'todos', fn($q) => $q->whereMonth('users.fecha_ingreso', $this->filtroMes))
            ->when($this->filtroQuincena === '1', fn($q) => $q->whereDay('users.fecha_ingreso', '>=', 1)->whereDay('users.fecha_ingreso', '<=', 15))
            ->when($this->filtroQuincena === '2', fn($q) => $q->whereDay('users.fecha_ingreso', '>=', 16))
            ->whereRaw('TIMESTAMPDIFF(YEAR, users.fecha_ingreso, CURDATE()) >= 1')
            ->leftJoin('solicitud_altas', 'users.sol_alta_id', '=', 'solicitud_altas.id')
            ->select('users.*')
            ->orderBy('users.fecha_ingreso', 'asc')
            ->orderBy('solicitud_altas.apellido_paterno', 'asc')
            ->orderBy('solicitud_altas.apellido_materno', 'asc')
            ->orderBy('solicitud_altas.nombre', 'asc')
            ->paginate(10);

        return view('livewire.antiguedades', compact('usuarios'));
    }

    public function generarExcel()
    {
        $usuarios = User::query()
            ->with(['solicitudAlta', 'documentacionAltas'])
            ->where('estatus', 'Activo')
            ->when($this->filtroMes !== 'todos', fn($q) => $q->whereMonth('fecha_ingreso', $this->filtroMes))
            ->when($this->filtroQuincena === '1', fn($q) => $q->whereDay('fecha_ingreso', '>=', 1)->whereDay('fecha_ingreso', '<=', 15))
            ->when($this->filtroQuincena === '2', fn($q) => $q->whereDay('fecha_ingreso', '>=', 16))
            ->whereRaw('TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) >= 1')
            ->leftJoin('solicitud_altas', 'users.sol_alta_id', '=', 'solicitud_altas.id')
            ->select('users.*')
            ->orderBy('fecha_ingreso', 'asc')
            ->orderBy('solicitud_altas.apellido_paterno', 'asc')
            ->orderBy('solicitud_altas.apellido_materno', 'asc')
            ->orderBy('solicitud_altas.nombre', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['No.', 'Empresa', 'Nombre', 'Sueldo', 'Fecha Ingreso', 'Antigüedad', 'Días', 'Salario Diario', '$ Vacaciones', 'Prima Vacacional']
        ], null, 'A1');

        $row = 2;
        foreach ($usuarios as $index => $usuario) {
            $fechaIngreso = Carbon::parse($usuario->fecha_ingreso);
            $antiguedad = $fechaIngreso->diff(now());
            $diasVacaciones = match (true) {
                $antiguedad->y < 2 => 12, $antiguedad->y === 2 => 14, $antiguedad->y === 3 => 16,
                $antiguedad->y === 4 => 18, $antiguedad->y === 5 => 20, $antiguedad->y > 5 && $antiguedad->y <= 10 => 22,
                $antiguedad->y > 10 && $antiguedad->y <= 15 => 24, $antiguedad->y > 15 && $antiguedad->y <= 20 => 26,
                $antiguedad->y > 20 && $antiguedad->y <= 25 => 28, $antiguedad->y > 25 && $antiguedad->y <= 30 => 30, default => 32,
            };
            $rawSueldo = $usuario->solicitudAlta->sueldo_mensual ?? '0';
            $soloNumero = preg_match('/\((.*?)\)/', $rawSueldo, $m) ? preg_replace('/[^0-9.]/', '', $m[1]) : preg_replace('/[^0-9.]/', '', $rawSueldo);
            $salario = floatval($soloNumero) / 2;
            $salarioDiario = $salario > 0 ? round($salario / 15, 2) : 0;
            $prima = round($salarioDiario * $diasVacaciones * 0.25, 2);

            $sheet->fromArray([
                $index + 1, $usuario->empresa ?? '—', $usuario->name, number_format($salario, 2),
                $fechaIngreso->format('d/m/Y'), $antiguedad->y . ' ' . ($antiguedad->y == 1 ? 'Año' : 'Años'),
                $diasVacaciones, number_format($salarioDiario, 2), number_format($diasVacaciones * $salarioDiario, 2), number_format($prima, 2)
            ], null, 'A' . $row);
            $row++;
        }

        $fileName = 'antiguedades_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
        $filePath = 'public/excel/' . $fileName;
        $writer = new Xlsx($spreadsheet);
        Storage::put($filePath, '');
        return response()->download(Storage::path($filePath))->deleteFileAfterSend(true);
    }

    public function updatedFiltroQuincena() { $this->resetPage(); }
    public function updatedFiltroMes() { $this->resetPage(); }
}
