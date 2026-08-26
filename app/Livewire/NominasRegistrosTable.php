<?php

namespace App\Livewire;

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Archivonomina;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class NominasRegistrosTable extends Component
{
    public function boot(): void
    {
        Gate::authorize(Permission::PAYROLL_VIEW);
    }

    use WithPagination, WithFileUploads;

    public $search = '';
    public $anio = '';
    public $mes = '';
    public $orden = 'created_at';
    public $direccion = 'desc';

    // Propiedades para edición
    public $editandoId = null;
    public $periodo;

    // Propiedades CALCULADAS (no editables)
    public $subtotalCalculado = 0;
    public $totalDestajosCalculado = 0;

    // Archivos
    public $arch_nomina;
    public $arch_nomina_spyt;
    public $arch_nomina_montana;
    public $arch_destajo;

    protected $queryString = [
        'search' => ['except' => ''],
        'anio' => ['except' => ''],
        'mes' => ['except' => ''],
        'orden' => ['except' => 'created_at'],
        'direccion' => ['except' => 'desc'],
        'page' => ['except' => 1],
    ];

    protected $rules = [
        'periodo' => 'required|string|max:255',
        'arch_nomina' => 'nullable|mimes:xlsx,xls,csv|max:10240',
        'arch_nomina_spyt' => 'nullable|mimes:xlsx,xls,csv|max:10240',
        'arch_nomina_montana' => 'nullable|mimes:xlsx,xls,csv|max:10240',
        'arch_destajo' => 'nullable|mimes:xlsx,xls,csv|max:10240',
    ];

    public function mount()
    {
        $this->anio = now()->year;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingAnio()
    {
        $this->resetPage();
    }

    public function updatingMes()
    {
        $this->resetPage();
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

    public function abrirModalEdicion($id)
    {
        $registro = Archivonomina::findOrFail($id);
        $this->editandoId = $registro->id;
        $this->periodo = $registro->periodo;

        // Inicializar valores calculados con los actuales
        $this->subtotalCalculado = $registro->subtotal ?? 0;
        $this->totalDestajosCalculado = $registro->total_destajos ?? 0;

        // Resetear archivos
        $this->arch_nomina = null;
        $this->arch_nomina_spyt = null;
        $this->arch_nomina_montana = null;
        $this->arch_destajo = null;
    }

public function guardarEdicion()
{
    \Log::info('=== INICIO guardarEdicion ===');
    \Log::info('Datos recibidos:', [
        'editandoId' => $this->editandoId,
        'periodo' => $this->periodo,
        'arch_nomina' => $this->arch_nomina?->getClientOriginalName(),
        'arch_nomina_spyt' => $this->arch_nomina_spyt?->getClientOriginalName(),
        'arch_nomina_montana' => $this->arch_nomina_montana?->getClientOriginalName(),
        'arch_destajo' => $this->arch_destajo?->getClientOriginalName(),
    ]);

    $this->validate();
    \Log::info('Validación exitosa');

    $registro = Archivonomina::findOrFail($this->editandoId);
    \Log::info('Registro cargado:', [
        'id' => $registro->id,
        'arch_nomina_actual' => $registro->arch_nomina,
        'arch_destajo_actual' => $registro->arch_destajo,
    ]);

    $rutaDirectorio = 'archivos_nominas/' . $this->periodo;
    Storage::disk('public')->makeDirectory($rutaDirectorio);
    \Log::info('Directorio asegurado:', ['ruta' => $rutaDirectorio]);

    // Procesar cada archivo
    $archivos = [
        'arch_nomina' => 'nomina',
        'arch_nomina_spyt' => 'nomina',
        'arch_nomina_montana' => 'nomina',
        'arch_destajo' => 'destajo'
    ];

    foreach ($archivos as $campo => $tipo) {
        \Log::info("Procesando archivo: {$campo}", [
            'subido' => $this->{$campo} ? 'SÍ' : 'NO',
            'archivo_actual' => $registro->{$campo}
        ]);

        if ($this->{$campo}) {
            // Eliminar anterior
            if ($registro->{$campo}) {
                $eliminado = Storage::disk('public')->delete($registro->{$campo});
                \Log::info("Archivo anterior eliminado: {$campo}", [
                    'ruta' => $registro->{$campo},
                    'resultado' => $eliminado ? 'ÉXITO' : 'FALLÓ'
                ]);
            }

            // Generar nombre limpio
            $nombreOriginal = $this->{$campo}->getClientOriginalName();
            $nombreLimpio = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreOriginal);
            $nombreUnico = pathinfo($nombreLimpio, PATHINFO_FILENAME) . '_' . now()->timestamp . '.' . $this->{$campo}->extension();

            // Guardar nuevo
            $rutaGuardada = $this->{$campo}->storeAs($rutaDirectorio, $nombreUnico, 'public');
            $registro->{$campo} = $rutaGuardada;

            \Log::info("Archivo nuevo guardado: {$campo}", [
                'nombre_original' => $nombreOriginal,
                'nombre_guardado' => $nombreUnico,
                'ruta_completa' => $rutaGuardada,
                'existe_en_disco' => file_exists(storage_path('app/public/' . $rutaGuardada)) ? 'SÍ' : 'NO'
            ]);
        } else {
            \Log::info("No se subió nuevo archivo para: {$campo}. Se conserva el actual.", [
                'archivo_actual' => $registro->{$campo}
            ]);
        }
    }

    // Calcular subtotales
    \Log::info('Iniciando cálculo de subtotales...');
    $subtotalNominas = 0;
    $totalDestajos = 0;

    foreach (['arch_nomina', 'arch_nomina_spyt', 'arch_nomina_montana'] as $campo) {
        if ($registro->{$campo}) {
            $subtotal = $this->calcularSubtotalNomina($registro->{$campo}, 'nomina');
            $subtotalNominas += $subtotal;
            \Log::info("Subtotal calculado para {$campo}", [
                'ruta' => $registro->{$campo},
                'subtotal' => $subtotal
            ]);
        }
    }

    if ($registro->arch_destajo) {
        $totalDestajos = $this->calcularSubtotalNomina($registro->arch_destajo, 'destajo');
        \Log::info("Total destajos calculado", [
            'ruta' => $registro->arch_destajo,
            'total' => $totalDestajos
        ]);
    }

    // ✅ CORRECCIÓN: Actualizar campos en el modelo y usar save()
    $registro->periodo = $this->periodo;
    $registro->subtotal = $subtotalNominas;
    $registro->total_destajos = $totalDestajos;
    $registro->save(); // ← Esto garantiza que se guarden todos los cambios

    \Log::info('Registro actualizado en BD', [
        'id' => $registro->id,
        'subtotal' => $subtotalNominas,
        'total_destajos' => $totalDestajos,
        'archivos' => [
            'nomina' => $registro->arch_nomina,
            'spyt' => $registro->arch_nomina_spyt,
            'montana' => $registro->arch_nomina_montana,
            'destajo' => $registro->arch_destajo,
        ]
    ]);

    $this->dispatch('actualizado', ['mensaje' => 'Registro actualizado correctamente.']);
    $this->resetPage();
    $this->cerrarModal();

    \Log::info('=== FIN guardarEdicion ===');
}

    public function cerrarModal()
    {
        $this->editandoId = null;
        $this->reset([
            'periodo', 'subtotalCalculado', 'totalDestajosCalculado',
            'arch_nomina', 'arch_nomina_spyt', 'arch_nomina_montana', 'arch_destajo'
        ]);
        $this->resetErrorBag();
    }

    // === Tu lógica de cálculo (sin cambios) ===
    private function calcularSubtotalNomina($rutaArchivo, $tipo = 'nomina')
    {
        try {
            $rutaCompleta = storage_path('app/public/' . $rutaArchivo);
            if (!file_exists($rutaCompleta)) {
                return 0;
            }

            $tipoArchivo = \PhpOffice\PhpSpreadsheet\IOFactory::identify($rutaCompleta);
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($tipoArchivo);
            $reader->setReadDataOnly(true);
            $reader->setLoadAllSheets();
            $spreadsheet = $reader->load($rutaCompleta);

            $nombresHojas = $spreadsheet->getSheetNames();
            $totalGeneral = 0;

            // === MANEJO DE DESTAJOS (soporta DOS formatos) ===
            if ($tipo === 'destajo') {
                $hojaResumen = null;
                foreach ($nombresHojas as $nombreHoja) {
                    if (strtoupper(trim($nombreHoja)) === 'RESUMEN') {
                        $hojaResumen = $spreadsheet->getSheetByName($nombreHoja);
                        break;
                    }
                }

                if ($hojaResumen) {
                    $dimension = $hojaResumen->getHighestRowAndColumn();
                    $maxRow = min((int)$dimension['row'], 100); // Buscar en primeras 100 filas
                    $maxColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($dimension['column']);

                    for ($row = 1; $row <= $maxRow; $row++) {
                        for ($colIndex = 1; $colIndex <= $maxColIndex; $colIndex++) {
                            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                            $cellValue = $hojaResumen->getCell("{$colLetter}{$row}")->getValue();

                            // Verificar si la celda contiene "TOTAL DESTAJO" (case-insensitive)
                            if (is_string($cellValue) && preg_match('/^\s*TOTAL\s+DESTAJO\s*$/i', $cellValue)) {
                                $nextColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                                $montoCell = $hojaResumen->getCell("{$nextColLetter}{$row}");
                                $monto = $montoCell->getCalculatedValue();

                                if (is_numeric($monto)) {
                                    $totalGeneral = (float)$monto;
                                    // Liberar recursos y retornar
                                    $spreadsheet->disconnectWorksheets();
                                    unset($spreadsheet, $reader, $hojaResumen);
                                    gc_collect_cycles();
                                    return $totalGeneral;
                                }
                            }
                        }
                    }
                }

                foreach ($nombresHojas as $nombreHoja) {
                    $worksheet = $spreadsheet->getSheetByName($nombreHoja);
                    $fila = 5;
                    $espaciosBlancoSeguidos = 0;
                    while ($espaciosBlancoSeguidos < 3 && $fila <= 1500) {
                        $nombreEmpleado = $worksheet->getCell('B' . $fila)->getValue();
                        $valorP = $worksheet->getCell('P' . $fila)->getCalculatedValue();
                        if (empty(trim((string)$nombreEmpleado))) {
                            $espaciosBlancoSeguidos++;
                        } else {
                            $espaciosBlancoSeguidos = 0;
                            if (is_numeric($valorP)) {
                                $totalGeneral += (float)$valorP;
                            }
                        }
                        $fila++;
                    }
                }
            }
            elseif ($tipo === 'nomina') {
                foreach ($nombresHojas as $nombreHoja) {
                    $worksheet = $spreadsheet->getSheetByName($nombreHoja);
                    $dimension = $worksheet->getHighestRowAndColumn();

                    $columnaNeto = null;
                    $filaEncabezadoEncontrada = null;
                    for ($filaEncabezado = 7; $filaEncabezado <= 9; $filaEncabezado++) {
                        for ($col = 'A'; $col <= 'Z'; $col++) {
                            $celda = $worksheet->getCell("{$col}{$filaEncabezado}")->getValue();
                            if (!$celda) continue;
                            $textoLimpio = strtoupper(trim($celda));
                            $textoLimpio = preg_replace('/[^A-Z0-9\s]/', ' ', $textoLimpio);
                            $textoLimpio = preg_replace('/\s+/', ' ', $textoLimpio);
                            if (str_contains($textoLimpio, 'NETO')) {
                                $palabrasProhibidas = ['AJUSTE', 'AJUSTES', 'POR PAGAR', 'PAGO', 'DESCUENTO'];
                                $tieneProhibida = false;
                                foreach ($palabrasProhibidas as $prohibida) {
                                    if (str_contains($textoLimpio, $prohibida)) {
                                        $tieneProhibida = true;
                                        break;
                                    }
                                }
                                if (!$tieneProhibida) {
                                    $columnaNeto = $col;
                                    $filaEncabezadoEncontrada = $filaEncabezado;
                                    break 2;
                                }
                            }
                        }
                    }

                    if (!$columnaNeto) continue;

                    $ultimoValorValido = 0;
                    $ultimaFilaConDatos = (int)$dimension['row'];
                    $fin = min($ultimaFilaConDatos, 1500);
                    $inicio = $filaEncabezadoEncontrada + 1;

                    $buscarEnColumna = function ($col) use ($worksheet, $inicio, $fin) {
                        $ultimoValor = 0;
                        for ($fila = $inicio; $fila <= $fin; $fila++) {
                            $valor = $worksheet->getCell("{$col}{$fila}")->getCalculatedValue();
                            if (is_numeric($valor) && !empty($valor)) {
                                $ultimoValor = (float)$valor;
                            }
                        }
                        return $ultimoValor;
                    };

                    $ultimoValorValido = $buscarEnColumna($columnaNeto);

                    if ($ultimoValorValido == 0) {
                        $colIndex = array_search($columnaNeto, range('A', 'Z'));
                        if ($colIndex !== false) {
                            for ($i = $colIndex + 1; $i < 26; $i++) {
                                $colAdyacente = chr(65 + $i);
                                $valor = $buscarEnColumna($colAdyacente);
                                if ($valor > 0) {
                                    $ultimoValorValido = $valor;
                                    break;
                                }
                            }
                        }
                    }

                    $totalGeneral += $ultimoValorValido;
                }
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $reader);
            gc_collect_cycles();

            return $totalGeneral;

        } catch (\Exception $e) {
            if (isset($spreadsheet)) {
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
            }
            gc_collect_cycles();
            return 0;
        }
    }

    // === Render ===
    public function render()
    {
        $registros = Archivonomina::query();

        if (!empty($this->search)) {
            $registros->where(function($query) {
                $query->where('periodo', 'like', '%' . $this->search . '%')
                      ->orWhere('arch_nomina', 'like', '%' . $this->search . '%')
                      ->orWhere('arch_destajo', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->anio)) {
            $registros->whereYear('created_at', $this->anio);
        }

        if (!empty($this->mes)) {
            $registros->where('periodo', 'like', '%' . ucfirst($this->mes) . '%');
        }

        $registros->orderBy($this->orden, $this->direccion);
        $registros = $registros->paginate(10);

        return view('livewire.nominas-registros-table', [
            'registros' => $registros,
            'anios' => $this->obtenerAniosDisponibles()
        ]);
    }

    private function obtenerAniosDisponibles()
    {
        return Archivonomina::selectRaw('YEAR(created_at) as anio')
            ->distinct()
            ->orderBy('anio', 'desc')
            ->pluck('anio')
            ->toArray();
    }
}
