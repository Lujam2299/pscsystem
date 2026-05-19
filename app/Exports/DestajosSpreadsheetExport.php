<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Asistencia;
use App\Models\Punto;
use App\Models\Subpunto;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\CalculoDestajoService;

class DestajosSpreadsheetExport
{
    protected $punto;
    protected $fechaInicio;
    protected $fechaFin;

    private CalculoDestajoService $destajoService;

    public function __construct($punto = null, $fechaInicio = null, $fechaFin = null)
    {
        $this->punto = $punto;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->destajoService = app(CalculoDestajoService::class);
    }

    public function generateFile()
    {
        $datos = $this->obtenerDatos();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Destajos');

        // ============================================
        // ENCABEZADOS PRINCIPALES (10 columnas fijas)
        // ============================================
        $columnasBase = [
            'No.', 'Nombre', 'Días Lab.', 'Desc.', 'Faltas', 'Incap.', 'PE-CG', 'PE-SG', 'Tarifa Diaria', 'TOTAL DESTAJO'
        ];
        $baseColumnCount = count($columnasBase); // = 10

        for ($index = 0; $index < $baseColumnCount; $index++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $titulo = $columnasBase[$index];

            $sheet->setCellValue("{$col}1", $titulo);
            $sheet->mergeCells("{$col}1:{$col}2");

            if ($index <= 1) {
                $fillColor = 'E0E0E0'; $fontColor = '000000';
            } elseif ($index <= 5) {
                $fillColor = 'FFF9C4'; $fontColor = '000000';
            } elseif ($index == 6) {
                $fillColor = 'E1BEE7'; $fontColor = '000000'; // PE-CG Morado
            } elseif ($index == 7) {
                $fillColor = 'BDBDBD'; $fontColor = '000000'; // PE-SG Gris
            } elseif ($index == 8) {
                $fillColor = 'C8E6C9'; $fontColor = '000000';
            } else {
                $fillColor = '81C784'; $fontColor = '000000';
            }

            $style = [
                'font' => ['name' => 'Century Gothic', 'size' => 9, 'bold' => true, 'color' => ['rgb' => $fontColor]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ];

            if ($titulo === strtoupper($titulo) && strlen($titulo) > 4 && !str_contains($titulo, ' ')) {
                $style['alignment']['textRotation'] = 90;
            }

            $sheet->getStyle("{$col}1")->applyFromArray($style);
        }

        // ============================================
        // ENCABEZADOS DE FECHAS
        // ============================================
        $diasSemanaES = [
            'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo',
        ];

        $colIndex = $baseColumnCount + 1;
        $currentDate = Carbon::parse($this->fechaInicio);
        $endDate = Carbon::parse($this->fechaFin);

        while ($currentDate->lte($endDate)) {
            $diaIngles = $currentDate->format('l');
            $diaEspanol = $diasSemanaES[$diaIngles] ?? $diaIngles;
            $numeroDia = $currentDate->format('d');
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);

            $sheet->setCellValue("{$colLetter}1", "$diaEspanol\n$numeroDia");
            $sheet->mergeCells("{$colLetter}1:{$colLetter}2");

            $sheet->getStyle("{$colLetter}1")->applyFromArray([
                'font' => ['name' => 'Century Gothic', 'size' => 8, 'bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true, 'textRotation' => 0],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFD54F']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ]);

            $sheet->getColumnDimension($colLetter)->setWidth(6);
            $colIndex++;
            $currentDate->addDay();
        }

        $sheet->getRowDimension(1)->setRowHeight(50);
        $sheet->getRowDimension(2)->setRowHeight(15);

        // ============================================
        // FILAS DE DATOS POR USUARIO
        // ============================================
        $row = 3;
        $totalGeneralDestajo = 0;
        $totalGeneralDiasLab = 0;
        $totalPE_CG = 0;
        $totalPE_SG = 0;

        foreach ($datos['usuarios'] as $user) {
            $destajoData = $datos['destajosPorUsuario'][$user->id] ?? null;
            if (!$destajoData || !$destajoData['success']) continue;

            $conteos = $destajoData['conteos'] ?? [];
            $desgloseDiario = $destajoData['desglose_diario'] ?? [];

            // Columnas A-H: Datos y conteos
            $sheet->setCellValue("A{$row}", $user->id);
            $sheet->setCellValue("B{$row}", strtoupper($user->name));
            $sheet->setCellValue("C{$row}", $destajoData['dias_laborados']);
            $sheet->setCellValue("D{$row}", $conteos['descansos'] ?? 0);
            $sheet->setCellValue("E{$row}", $conteos['faltas'] ?? 0);
            $sheet->setCellValue("F{$row}", $conteos['incapacidades'] ?? 0);
            $sheet->setCellValue("G{$row}", $conteos['permisos_cg'] ?? 0);  // PE-CG
            $sheet->setCellValue("H{$row}", $conteos['permisos_sg'] ?? 0);  // PE-SG
            $sheet->setCellValue("I{$row}", $destajoData['tarifa_diaria']);
            $sheet->setCellValue("J{$row}", $destajoData['total_monto']);

            // Formato de moneda
            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');
            $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('$#,##0.00');

            // Color para Total Destajo
            $sheet->getStyle("J{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C8E6C9']],
                'font' => ['bold' => true],
            ]);

            // Acumular totales
            $totalGeneralDiasLab += $destajoData['dias_laborados'];
            $totalGeneralDestajo += $destajoData['total_monto'];
            $totalPE_CG += $conteos['permisos_cg'] ?? 0;
            $totalPE_SG += $conteos['permisos_sg'] ?? 0;

            // ============================================
            // COLUMNAS DIARIAS (Desglose visual)
            // ============================================
            $colDia = $baseColumnCount + 1; // = 11 (Columna K)
            $fechaIter = Carbon::parse($this->fechaInicio);

            while ($fechaIter->lte(Carbon::parse($this->fechaFin))) {
                $fechaStr = $fechaIter->format('Y-m-d');
                $codigo = $desgloseDiario[$fechaStr] ?? '';
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colDia);

                $sheet->setCellValue("{$colLetter}{$row}", $codigo);

                $colorRGB = 'FFFFFF';
                if ($codigo === 'A') $colorRGB = '58D68D';
                elseif ($codigo === 'D') $colorRGB = 'F9E79F';
                elseif ($codigo === 'F') $colorRGB = 'F5B7B1';
                elseif ($codigo === 'I') $colorRGB = 'F8D7DA';
                elseif ($codigo === 'V') $colorRGB = 'A9CCE3';
                elseif (str_starts_with($codigo, 'PE-CG')) $colorRGB = 'D2B4DE';
                elseif (str_starts_with($codigo, 'PE-SG')) $colorRGB = 'D5DBDB';
                elseif (str_starts_with($codigo, 'R')) $colorRGB = 'FCF3CF';

                $sheet->getStyle("{$colLetter}{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorRGB]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'font' => ['size' => 9, 'bold' => in_array($codigo, ['A', 'D'])],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
                ]);

                $colDia++;
                $fechaIter->addDay();
            }

            // Bordes para toda la fila
            $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colDia - 1);
            $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ]);

            $row++;
        }

        // ============================================
        // FILA DE TOTALES GENERALES
        // ============================================
        $lastColIndex = $colIndex;
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIndex - 1);

        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'TOTALES GENERALES');
        $sheet->setCellValue("C{$row}", $totalGeneralDiasLab);
        $sheet->setCellValue("D{$row}", array_sum(array_map(fn($d) => ($d['conteos']['descansos'] ?? 0), $datos['destajosPorUsuario'])));
        $sheet->setCellValue("E{$row}", array_sum(array_map(fn($d) => ($d['conteos']['faltas'] ?? 0), $datos['destajosPorUsuario'])));
        $sheet->setCellValue("F{$row}", array_sum(array_map(fn($d) => ($d['conteos']['incapacidades'] ?? 0), $datos['destajosPorUsuario'])));
        $sheet->setCellValue("G{$row}", $totalPE_CG);
        $sheet->setCellValue("H{$row}", $totalPE_SG);
        $sheet->setCellValue("J{$row}", $totalGeneralDestajo);

        $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BDBDBD']],
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);

        $sheet->getStyle("C{$row}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF9C4']],
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle("J{$row}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '81C784']],
            'font' => ['bold' => true, 'size' => 11],
            'numberFormat' => ['formatCode' => '$#,##0.00'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);

        $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']]],
        ]);

        // ============================================
        // FORMATOS GENERALES
        // ============================================
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(8);
        $sheet->getColumnDimension('E')->setWidth(8);
        $sheet->getColumnDimension('F')->setWidth(8);
        $sheet->getColumnDimension('G')->setWidth(8);
        $sheet->getColumnDimension('H')->setWidth(8);
        $sheet->getColumnDimension('I')->setWidth(14);
        $sheet->getColumnDimension('J')->setWidth(16);

        $sheet->freezePane('C3');

        $dataRange = "A1:{$lastColLetter}" . ($row);
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']]],
        ]);

        // ============================================
        // GUARDAR Y DESCARGAR
        // ============================================
        $writer = new Xlsx($spreadsheet);
        $fileName = 'destajos_' . $this->fechaInicio . '_al_' . $this->fechaFin . '.xlsx';
        $tempPath = tempnam(sys_get_temp_dir(), 'destajos_export_');

        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    // ============================================
    // MÉTODOS AUXILIARES
    // ============================================
    private function obtenerDatos()
    {
        if (!$this->fechaInicio || !$this->fechaFin) {
            return ['usuarios' => collect(), 'fechas' => [], 'destajosPorUsuario' => []];
        }

        $filtro = $this->punto ? strtoupper($this->punto) : '';
        if (in_array($filtro, ['MARYKAY CORPORATIVO', 'MAR KAY CORPORATIVO'])) {
            $filtro = 'MARY KAY CORPORATIVO';
        }

        $puntoGeneral = null;
        $subpuntos = [];
        $mapaSubpuntos = $this->getSubpuntosPorPunto();

        foreach ($mapaSubpuntos as $p => $subs) {
            if ($filtro === $p) {
                $puntoGeneral = $p;
                $subpuntos = $subs;
                break;
            } elseif (collect($subs)->pluck('nombre')->map('strtoupper')->contains($filtro)) {
                $puntoGeneral = $p;
                $subpuntos = [collect($subs)->firstWhere('nombre', 'LIKE', $filtro)];
                break;
            } elseif (collect($subs)->pluck('codigo')->map('strval')->contains($filtro)) {
                $puntoGeneral = $p;
                $subpuntos = [collect($subs)->firstWhere('codigo', $filtro)];
                break;
            }
        }

        if (!$puntoGeneral && in_array($filtro, ['MARYKAY CORPORATIVO', 'MARY KAY CORPORATIVO'])) {
            $puntoGeneral = 'MONTERREY';
            $subpuntos = [collect($mapaSubpuntos['MONTERREY'])->firstWhere('nombre', 'LIKE', $filtro)];
        }

        if (!$puntoGeneral) {
            $puntoGeneral = $filtro;
            $subpuntos = [['nombre' => $filtro, 'codigo' => null]];
        }

        $rolAuth = Auth::user()?->rol;
        if ($rolAuth === 'AUXILIAR OPERACIONES') {
            $puntoGeneral = 'MONTERREY';
            $subpuntos = $mapaSubpuntos['MONTERREY'];
        }

        if ($filtro === 'MONTERREY' || $rolAuth === 'AUXILIAR OPERACIONES') {
            $monterreySubpuntos = collect($mapaSubpuntos['MONTERREY'])->pluck('nombre')->toArray();
            $puntosAsistencias = array_merge(['MONTERREY'], $monterreySubpuntos, ['KANSAS', 'MTY']);
        } else {
            $puntosAsistencias = [$filtro];
        }

        $asistenciasIndexadas = Asistencia::whereIn('punto', $puntosAsistencias)
            ->whereBetween('fecha', [$this->fechaInicio, $this->fechaFin])
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->fecha)->format('Y-m-d'));

        $usuarios = User::where('estatus', 'Activo')
            ->where(function ($query) use ($subpuntos, $puntoGeneral) {
                foreach ($subpuntos as $subpunto) {
                    $nombre = $subpunto['nombre'] ?? null;
                    $codigo = $subpunto['codigo'] ?? null;
                    $query->orWhere(function ($q) use ($nombre, $codigo, $puntoGeneral) {
                        if ($nombre) {
                            $q->whereRaw('LOWER(punto) LIKE ?', ['%' . strtolower($nombre) . '%']);
                            if ($nombre === 'MARY KAY CORPORATIVO') {
                                $q->orWhereRaw('LOWER(punto) LIKE ?', ['%marykay corporativo%'])
                                  ->orWhereRaw('LOWER(punto) LIKE ?', ['%mar kay corporativo%']);
                            }
                        }
                        if ($codigo && $puntoGeneral === 'MONTERREY') {
                            $q->orWhere('punto', $codigo);
                        }
                    });
                }
            });

        if ($filtro === 'MONTERREY' || $rolAuth === 'AUXILIAR OPERACIONES') {
            $usuarios->orWhere(function ($q) {
                $q->where('punto', 'KANSAS')->orWhere('punto', 'MTY');
            });
        }

        $usuarios = $usuarios->get()->sortBy(['punto', 'asc', 'name', 'asc']);

        $fechas = [];
        $startDate = Carbon::parse($this->fechaInicio);
        $endDate = Carbon::parse($this->fechaFin);
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $fechas[] = $date->format('Y-m-d');
        }

        $vacacionesPorUsuario = [];
        foreach ($usuarios as $user) {
            $vacaciones = DB::table('solicitud_vacaciones')
                ->where('user_id', $user->id)
                ->where('estatus', 'Aceptada')
                ->where(function ($query) {
                    $query->whereBetween('fecha_inicio', [$this->fechaInicio, $this->fechaFin])
                        ->orWhereBetween('fecha_fin', [$this->fechaInicio, $this->fechaFin]);
                })
                ->get();
            $dias = collect();
            foreach ($vacaciones as $vac) {
                $inicio = Carbon::parse($vac->fecha_inicio);
                $fin = Carbon::parse($vac->fecha_fin);
                for ($d = $inicio->copy(); $d->lte($fin); $d->addDay()) {
                    $dias->push($d->format('Y-m-d'));
                }
            }
            $vacacionesPorUsuario[$user->id] = $dias->toArray();
        }

        $permisosPorUsuario = [];
        $permisos = \App\Models\PermisoEspecial::where(function($q) {
            $q->whereBetween('fecha_inicio', [$this->fechaInicio, $this->fechaFin])
              ->orWhereBetween('fecha_fin', [$this->fechaInicio, $this->fechaFin]);
        })->get();

        foreach ($permisos as $permiso) {
            $inicio = Carbon::parse($permiso->fecha_inicio);
            $fin = Carbon::parse($permiso->fecha_fin);
            for ($d = $inicio->copy(); $d->lte($fin); $d->addDay()) {
                $fecha = $d->format('Y-m-d');
                $permisosPorUsuario[$permiso->user_id][$fecha] = [
                    'con_goce' => (int) $permiso->con_goce === 1
                ];
            }
        }

        $incapacidadesPorUsuario = [];
        $incapacidades = \App\Models\Incapacidad::where(function ($q) {
            $q->whereBetween('fecha_inicio', [$this->fechaInicio, $this->fechaFin]);
        })
        ->orWhere(function ($q) {
            $q->whereDate(\DB::raw('DATE_ADD(fecha_inicio, INTERVAL dias_incapacidad - 1 DAY)'), '>=', $this->fechaInicio)
              ->where('fecha_inicio', '<=', $this->fechaFin);
        })->get();

        foreach ($incapacidades as $inc) {
            $inicio = Carbon::parse($inc->fecha_inicio);
            $fin = $inicio->copy()->addDays($inc->dias_incapacidad - 1);
            for ($d = $inicio->copy(); $d->lte($fin); $d->addDay()) {
                $fecha = $d->format('Y-m-d');
                if (Carbon::parse($fecha)->between(Carbon::parse($this->fechaInicio), Carbon::parse($this->fechaFin))) {
                    $incapacidadesPorUsuario[$inc->user_id][] = $fecha;
                }
            }
        }

        $destajosPorUsuario = [];
        foreach ($usuarios as $user) {
            try {
                $resultado = $this->destajoService->calcularDestajo(
                    $user,
                    $this->fechaInicio,
                    $this->fechaFin,
                    [
                        'vacacionesPorUsuario' => $vacacionesPorUsuario,
                        'asistenciasIndexadas' => $asistenciasIndexadas,
                        'permisosPorUsuario' => $permisosPorUsuario,
                        'incapacidadesPorUsuario' => $incapacidadesPorUsuario,
                    ]
                );
                if ($resultado['success']) {
                    $destajosPorUsuario[$user->id] = $resultado;
                }
            } catch (\Exception $e) {
                // Silencioso
            }
        }

        return [
            'usuarios' => $usuarios,
            'fechas' => $fechas,
            'destajosPorUsuario' => $destajosPorUsuario,
        ];
    }

    protected function getSubpuntosPorPunto()
    {
        $monterreyId = Punto::where('nombre', 'MONTERREY')->value('id');
        $codigos = [];
        if ($monterreyId) {
            $codigos = Subpunto::where('punto_id', $monterreyId)->pluck('codigo', 'nombre')->toArray();
        }
        $codigoMaryKay = $codigos['MARY KAY CORPORATIVO'] ?? $codigos['MARYKAY CORPORATIVO'] ?? $codigos['MAR KAY CORPORATIVO'] ?? null;

        $monterreySubpuntos = [
            ['nombre' => 'MONTERREY', 'codigo' => $codigos['MONTERREY'] ?? null],
            ['nombre' => 'CUSTODIO', 'codigo' => $codigos['CUSTODIO'] ?? null],
            ['nombre' => 'DALTILE', 'codigo' => $codigos['DALTILE'] ?? null],
            ['nombre' => 'TORRENOVO', 'codigo' => $codigos['TORRENOVO'] ?? null],
            ['nombre' => 'TRASLADOS', 'codigo' => $codigos['TRASLADOS'] ?? null],
            ['nombre' => 'BONETERA', 'codigo' => $codigos['BONETERA'] ?? null],
            ['nombre' => 'HOMEDEPOT', 'codigo' => $codigos['HOMEDEPOT'] ?? null],
            ['nombre' => 'AMERICAN AIRLINES', 'codigo' => $codigos['AMERICAN AIRLINES'] ?? null],
            ['nombre' => 'MARY KAY CORPORATIVO', 'codigo' => $codigoMaryKay],
            ['nombre' => 'KANSAS', 'codigo' => $codigos['KANSAS'] ?? null],
            ['nombre' => 'CIMARRON', 'codigo' => $codigos['CIMARRON'] ?? null],
            ['nombre' => 'OFICINA', 'codigo' => $codigos['OFICINA'] ?? null],
            ['nombre' => 'ASSET', 'codigo' => $codigos['ASSET'] ?? null],
            ['nombre' => 'TORRE DELTA', 'codigo' => $codigos['TORRE DELTA'] ?? null],
            ['nombre' => 'SACMI DE MEXICO', 'codigo' => $codigos['SACMI DE MEXICO'] ?? null],
            ['nombre' => 'THERMO ELÉCTRICA', 'codigo' => $codigos['THERMO ELÉCTRICA'] ?? null],
            ['nombre' => 'KINDER MORGAN', 'codigo' => $codigos['KINDER MORGAN'] ?? null],
            ['nombre' => 'GOBAR', 'codigo' => $codigos['GOBAR'] ?? null],
            ['nombre' => 'PEMCORP #2', 'codigo' => $codigos['PEMCORP #2'] ?? null],
            ['nombre' => 'ROCHE BOBOIS', 'codigo' => $codigos['ROCHE BOBOIS'] ?? null],
            ['nombre' => 'OFF ON GREEN', 'codigo' => $codigos['OFF ON GREEN'] ?? null],
            ['nombre' => 'COOPER LIGHT', 'codigo' => $codigos['COOPER LIGHT'] ?? null],
            ['nombre' => 'MONTE PALATINO', 'codigo' => $codigos['MONTE PALATINO'] ?? null],
            ['nombre' => 'OATEY', 'codigo' => $codigos['OATEY'] ?? null],
            ['nombre' => 'PLAZA DOMENA', 'codigo' => $codigos['PLAZA DOMENA'] ?? null],
        ];

        return [
            'MONTERREY' => $monterreySubpuntos,
            'GUANAJUATO' => [['nombre' => 'SILAO', 'codigo' => null], ['nombre' => 'CELAYA', 'codigo' => null], ['nombre' => 'SALAMANCA', 'codigo' => null]],
            'NUEVO LAREDO' => [['nombre' => 'ZONA DE ABASTOS V', 'codigo' => null]],
            'MEXICO' => [['nombre' => 'VALLE DE MEXICO', 'codigo' => null]],
            'SLP' => [['nombre' => 'WATCO', 'codigo' => null], ['nombre' => 'BMW', 'codigo' => null], ['nombre' => 'ZONA DE ABASTOS I', 'codigo' => null], ['nombre' => 'INTERPUERTO Y TALLER', 'codigo' => null]],
            'XALAPA' => [['nombre' => 'XALAPA', 'codigo' => null]],
            'MICHOACAN' => [['nombre' => 'MICHOACÁN', 'codigo' => null]],
            'PUEBLA' => [['nombre' => 'PUEBLA', 'codigo' => null]],
            'TOLUCA' => [['nombre' => 'TOLUCA', 'codigo' => null]],
            'QUERETARO' => [['nombre' => 'QUERÉTARO', 'codigo' => null]],
            'SALTILLO' => [['nombre' => 'SALTILLO', 'codigo' => null]],
            'DRONES' => [['nombre' => 'DRONES', 'codigo' => null]],
        ];
    }

    protected function normalize($string)
    {
        return strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $string));
    }
}
