<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Asistencia;
use App\Models\TiemposExtra;
use App\Models\Punto;
use App\Models\Subpunto;
use App\Models\Retardo;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\CalculoNominaService;

class AsistenciasSpreadsheetExport
{
    protected $punto;
    protected $fechaInicio;
    protected $fechaFin;

    private CalculoNominaService $calculoService;

    public function __construct($punto = null, $fechaInicio = null, $fechaFin = null)
    {
        $this->punto = strtoupper($punto);
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->calculoService = app(CalculoNominaService::class);
    }

    public function generateFile()
    {
        $datos = $this->obtenerDatos();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Definir columnas base
        $columnasBase = [
            'No.', 'Nombre', 'Sueldo Qna', 'H.Extra', 'ASISTENCIAS', 'DESCANSOS', 'PERM.CG', 'PERM.SG', 'TE.HRS', 'FJ', 'FALTAS', 'INC', 'VACACI', 'Punto',
            'Sueldo Diario', 'Días Pagados', 'Bono Asist.', 'Bono Punt.', 'Hrs Extra', 'Subtotal', 'ISR', 'Total Neto'
        ];
        $baseColumnCount = count($columnasBase);

        // Escribir encabezados con colores por sección (una sola fila)
        for ($index = 0; $index < $baseColumnCount; $index++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $titulo = $columnasBase[$index];

            $sheet->setCellValue("{$col}1", $titulo);
            $sheet->mergeCells("{$col}1:{$col}2");

            // Determinar color según sección
            if ($index < 14) {
                // CONTEOS: columnas 1–14 (A–N)
                $fillColor = 'E0E0E0'; // Gris claro
                $fontColor = '000000';
            } elseif ($index < 20) {
                // PERCEPCIONES: columnas 15–20 (O–T)
                $fillColor = 'C8E6C9'; // Verde claro
                $fontColor = '000000';
            } else {
                // DEDUCCIONES: columnas 21–22 (U–V)
                $fillColor = 'FFCDD2'; // Rojo claro
                $fontColor = '000000';
            }

            $style = [
                'font' => [
                    'name' => 'Century Gothic',
                    'size' => 9,
                    'bold' => true,
                    'color' => ['rgb' => $fontColor],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $fillColor],
                ],
            ];

            // Rotar texto si es abreviatura larga (ej: "ASISTENCIAS")
            if ($titulo === strtoupper($titulo) && preg_match('/[A-Z]/', $titulo)) {
                $style['alignment']['textRotation'] = 90;
            }

            $sheet->getStyle("{$col}1")->applyFromArray($style);
        }
        $sheet->getRowDimension(1)->setRowHeight(60);

        // Ajustar ancho de columna "Nombre"
        $sheet->getColumnDimension('B')->setWidth(25);

        // Fechas (como en tu original)
        $start = new \DateTime($this->fechaInicio);
        $end = new \DateTime($this->fechaFin);
        $interval = $start->diff($end)->days + 1;

        $diasSemanaES = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo',
        ];

        for ($i = 0; $i < $interval; $i++) {
            $currentDate = clone $start;
            $currentDate->modify("+{$i} day");

            $diaIngles = $currentDate->format('l');
            $diaEspanol = $diasSemanaES[$diaIngles];
            $numeroDia = $currentDate->format('d');

            $colIndex = $baseColumnCount + ($i * 2) + 1;
            $col1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $col2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);

            $sheet->mergeCells("{$col1}1:{$col2}1");
            $sheet->setCellValue("{$col1}1", "$diaEspanol\n$numeroDia");
            $sheet->setCellValue("{$col1}2", '');
            $sheet->setCellValue("{$col2}2", '');

            $sheet->getStyle("{$col1}1:{$col2}2")->applyFromArray([
                'font' => ['name' => 'Century Gothic', 'size' => 9, 'bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFD54F'], // Amarillo claro para fechas
                ],
                'borders' => [
                    'left' => [
                        'borderStyle' => Border::BORDER_THICK,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);

            $sheet->getColumnDimension($col1)->setWidth(5);
            $sheet->getColumnDimension($col2)->setWidth(5);
        }

        $row = 3;
        foreach ($datos['usuarios'] as $user) {
            $nomina = $datos['nominaPorUsuario'][$user->id] ?? null;

            $sheet->setCellValue("A{$row}", $user->id);
            $sheet->setCellValue("B{$row}", $user->name);
            $sheet->setCellValue("C{$row}", ($this->normalize($user->rol) === 'guardia') ? '$5000.00' : '$5500.00');
            $sheet->getStyle("C{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');

            // H.Extra
            $totalHorasExtra = array_sum($datos['horasExtrasPorUsuario'][$user->id] ?? []);
            $sheet->setCellValue("D{$row}", $totalHorasExtra);
            if ($totalHorasExtra > 0) {
                $valor = (940 / 24) * $totalHorasExtra;
                $sheet->setCellValue("E{$row}", '$' . number_format($valor, 2));
                $sheet->getStyle("E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
            } else {
                $sheet->setCellValue("E{$row}", '0');
                $sheet->getStyle("E{$row}")->getFill()->setFillType(Fill::FILL_NONE);
            }

            // Contadores
            $asistencias = 0;
            $descansos = 0;
            $permisosConGoce = 0;
            $permisosSinGoce = 0;
            $teHoras = 0;
            $faltasJustificadas = 0;
            $faltas = 0;
            $inc = 0;
            $vacaciones = 0;

            foreach ($datos['fechas'] as $fecha) {
                $asistencia = $datos['asistenciasIndexadas']->get($fecha);
                $enlistados = json_decode($asistencia?->elementos_enlistados, true) ?? [];
                $faltantes = json_decode($asistencia?->faltas, true) ?? [];
                $descansantes = json_decode($asistencia?->descansos, true) ?? [];

                $asistio = in_array($user->id, $enlistados);
                $falto = in_array($user->id, $faltantes);
                $descanso = in_array($user->id, $descansantes);

                // Incapacidad
                $incapacidad = in_array($fecha, $datos['incapacidadesPorUsuario'][$user->id] ?? []);

                // Permiso
                $permiso = $datos['permisosPorUsuario'][$user->id][$fecha] ?? null;
                if ($permiso) {
                    if ((int)$permiso['con_goce'] === 1) {
                        $permisosConGoce++;
                    } else {
                        $permisosSinGoce++;
                    }
                    continue;
                }

                // Vacaciones
                if (in_array($fecha, $datos['vacacionesPorUsuario'][$user->id] ?? [])) {
                    $vacaciones++;
                    continue;
                }

                // Descanso
                if ($descanso) {
                    $descansos++;
                    continue;
                }

                // Falta
                if ($falto) {
                    $esJustificada = $datos['faltasJustificadas'][$user->id][$fecha] ?? false;
                    if ($esJustificada) {
                        $faltasJustificadas++;
                    } else {
                        $faltas++;
                    }
                    continue;
                }

                // Incapacidad
                if ($incapacidad) {
                    $inc++;
                    continue;
                }

                // Asistencia
                if ($asistio) {
                    $asistencias++;
                }
            }

            $sheet->setCellValue("E{$row}", $asistencias);
            $sheet->setCellValue("F{$row}", $descansos);
            $sheet->setCellValue("G{$row}", $permisosConGoce);
            $sheet->setCellValue("H{$row}", $permisosSinGoce);
            $sheet->setCellValue("I{$row}", $teHoras);
            $sheet->setCellValue("J{$row}", $faltasJustificadas);
            $sheet->setCellValue("K{$row}", $faltas);
            $sheet->setCellValue("L{$row}", $inc);
            $sheet->setCellValue("M{$row}", $vacaciones);
            $sheet->setCellValue("N{$row}", $user->punto);

            // 🔥 Nuevas columnas de nómina
            $sheet->setCellValue("O{$row}", $nomina['sueldo_diario'] ?? 0);
            $sheet->setCellValue("P{$row}", $nomina['dias_pagados']['total'] ?? 0);
            $sheet->setCellValue("Q{$row}", ($nomina['bonos']['asistencia']['aplica'] ?? false) ? $nomina['bonos']['asistencia']['monto'] ?? 0 : 0);
            $sheet->setCellValue("R{$row}", ($nomina['bonos']['puntualidad']['aplica'] ?? false) ? $nomina['bonos']['puntualidad']['monto'] ?? 0 : 0);
            $sheet->setCellValue("S{$row}", $nomina['horas_extra']['monto'] ?? 0);
            $sheet->setCellValue("T{$row}", $nomina['subtotal_percepciones'] ?? 0);
            $sheet->setCellValue("U{$row}", $nomina['isr'] ?? 0);
            $sheet->setCellValue("V{$row}", $nomina['total_neto'] ?? 0);

            // Turnos por día
            $colDia = $baseColumnCount + 1;
            $current = clone $start;
            for ($i = 0; $i < $interval; $i++) {
                $fechaStr = $current->format('Y-m-d');
                $asistencia = $datos['asistenciasIndexadas']->get($fechaStr);

                $asistio = false;
                $falto = false;
                $descanso = false;

                if ($asistencia) {
                    $enlistados = json_decode($asistencia->elementos_enlistados, true) ?? [];
                    $faltantes = json_decode($asistencia->faltas, true) ?? [];
                    $descansantes = json_decode($asistencia->descansos, true) ?? [];

                    $asistio = in_array($user->id, $enlistados);
                    $falto = in_array($user->id, $faltantes);
                    $descanso = in_array($user->id, $descansantes);
                }

                $incapacidad = in_array($fechaStr, $datos['incapacidadesPorUsuario'][$user->id] ?? []);
                $permiso = $datos['permisosPorUsuario'][$user->id][$fechaStr] ?? null;

                if ($permiso) {
                    $valorCelda = (int)$permiso['con_goce'] === 1 ? 'PE-CG' : 'PE-SG';
                } elseif (in_array($fechaStr, $datos['vacacionesPorUsuario'][$user->id] ?? [])) {
                    $valorCelda = 'V';
                } elseif ($descanso) {
                    $valorCelda = 'D';
                } elseif ($incapacidad) {
                    $valorCelda = 'I';
                } elseif ($falto) {
                    $esJustificada = $datos['faltasJustificadas'][$user->id][$fechaStr] ?? false;
                    $valorCelda = $esJustificada ? 'FJ' : 'F';
                } elseif ($asistio) {
                    $valorCelda = 'A';
                } else {
                    $valorCelda = '';
                }

                $cellCol1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colDia);
                $cellCol2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colDia + 1);

                $sheet->setCellValue("{$cellCol1}{$row}", $valorCelda);
                $sheet->setCellValue("{$cellCol2}{$row}", ''); // Turno tarde/noche vacío

                // Aplicar colores a celdas diarias
                if ($valorCelda === 'F') {
                    $sheet->getStyle("{$cellCol1}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('f5b7b1');
                } elseif ($valorCelda === 'FJ') {
                    $sheet->getStyle("{$cellCol1}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('58d68d');
                } elseif ($valorCelda === 'V') {
                    $sheet->getStyle("{$cellCol1}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('A9CCE3');
                } elseif ($valorCelda === 'D') {
                    $sheet->getStyle("{$cellCol1}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('f9e79f');
                } elseif ($valorCelda === 'A') {
                    $sheet->getStyle("{$cellCol1}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('58d68d');
                } elseif ($valorCelda === 'PE-CG') {
                    $sheet->getStyle("{$cellCol1}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('d2b4de');
                } elseif ($valorCelda === 'PE-SG') {
                    $sheet->getStyle("{$cellCol1}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('d5dbdb');
                } elseif ($valorCelda === 'I') {
                    $sheet->getStyle("{$cellCol1}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('f8d7da');
                } elseif (str_starts_with($valorCelda, 'R')) {
                    $sheet->getStyle("{$cellCol1}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('fcf3cf');
                } else {
                    $sheet->getStyle("{$cellCol1}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
                }

                $colDia += 2;
                $current->modify('+1 day');
            }

            $row++;
        }

        // Aplicar bordes a toda la tabla
        $lastColumnIndex = $colDia - 1;
        $lastColumnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColumnIndex);
        $lastRow = $row - 1;

        $range = "A1:{$lastColumnLetter}{$lastRow}";
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'reporte_asistencias_completo.xlsx';
        $tempPath = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    private function obtenerDatos()
    {
        if (!$this->fechaInicio || !$this->fechaFin) {
            return [
                'usuarios' => collect(),
                'fechas' => [],
                'vacacionesPorUsuario' => [],
                'asistenciasIndexadas' => collect(),
                'horasExtrasPorUsuario' => [],
                'permisosPorUsuario' => [],
                'faltasJustificadas' => [],
                'retardosPorUsuario' => [],
                'incapacidadesPorUsuario' => [],
                'nominaPorUsuario' => [],
            ];
        }

        $filtro = $this->punto;
        if (in_array(strtoupper($filtro), ['MARYKAY CORPORATIVO', 'MAR KAY CORPORATIVO'])) {
            $filtro = 'MARY KAY CORPORATIVO';
        }

        $puntoGeneral = null;
        $subpuntos = [];

        foreach ($this->getSubpuntosPorPunto() as $p => $subs) {
            if (strtoupper($filtro) === strtoupper($p)) {
                $puntoGeneral = $p;
                $subpuntos = $subs;
                break;
            } elseif (collect($subs)->pluck('nombre')->map('strtoupper')->contains(strtoupper($filtro))) {
                $puntoGeneral = $p;
                $subpuntos = [collect($subs)->firstWhere('nombre', 'LIKE', $filtro)];
                break;
            } elseif (collect($subs)->pluck('codigo')->map('strval')->contains($filtro)) {
                $puntoGeneral = $p;
                $subpuntos = [collect($subs)->firstWhere('codigo', $filtro)];
                break;
            }
        }

        if (!$puntoGeneral && in_array(strtoupper($filtro), ['MARYKAY CORPORATIVO', 'MARY KAY CORPORATIVO'])) {
            $puntoGeneral = 'MONTERREY';
            $subpuntos = [
                collect($this->getSubpuntosPorPunto()['MONTERREY'])->firstWhere('nombre', 'LIKE', $filtro)
            ];
        }

        if (!$puntoGeneral) {
            $puntoGeneral = $filtro;
            $subpuntos = [['nombre' => $filtro, 'codigo' => null]];
        }

        $rol = Auth::user()?->rol;
        if ($rol === 'AUXILIAR OPERACIONES') {
            $puntoGeneral = 'MONTERREY';
            $subpuntos = $this->getSubpuntosPorPunto()['MONTERREY'];
        }

        if ($filtro === 'MONTERREY') {
            $monterreySubpuntos = collect($this->getSubpuntosPorPunto()['MONTERREY'])->pluck('nombre')->toArray();
            $puntosAsistencias = array_merge(['MONTERREY'], $monterreySubpuntos, ['KANSAS', 'MTY']);
        } else {
            $puntosAsistencias = [$filtro];
        }

        $asistenciasIndexadas = Asistencia::with('puntosAsignados', 'usuario')
            ->whereIn('punto', $puntosAsistencias)
            ->whereBetween('fecha', [$this->fechaInicio, $this->fechaFin])
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->fecha)->format('Y-m-d'));

        $puntosAsignadosMap = [];
        foreach ($asistenciasIndexadas as $fecha => $asistencia) {
            if (in_array($asistencia->usuario->punto, ['KANSAS', 'MTY'])) {
                $puntosAsignadosMap[$fecha] = $asistencia->puntosAsignados->pluck('punto', 'user_id')->toArray();
            }
        }

        $this->puntosAsignadosMap = $puntosAsignadosMap;

        $usuarios = User::where('estatus', 'Activo')
            ->where(function ($query) use ($subpuntos, $puntoGeneral) {
                foreach ($subpuntos as $subpunto) {
                    $nombre = $subpunto['nombre'] ?? null;
                    $codigo = $subpunto['codigo'] ?? null;

                    $query->orWhere(function ($q) use ($nombre, $codigo, $puntoGeneral) {
                        if ($nombre) {
                            $q->whereRaw('LOWER(punto) LIKE ?', ['%' . strtolower($nombre) . '%']);
                        }
                        if ($nombre === 'MARY KAY CORPORATIVO') {
                            $q->orWhereRaw('LOWER(punto) LIKE ?', ['%' . strtolower($nombre) . '%'])
                              ->orWhereRaw('LOWER(punto) LIKE ?', ['%marykay corporativo%'])
                              ->orWhereRaw('LOWER(punto) LIKE ?', ['%mar kay corporativo%']);
                        }
                        if ($codigo && $puntoGeneral === 'MONTERREY') {
                            $q->orWhere('punto', $codigo);
                        }
                    });
                }
            });

        if ($filtro === 'MONTERREY') {
            $usuarios->orWhere(function ($q) {
                $q->where('punto', 'KANSAS')
                  ->orWhere('punto', 'MTY');
            });
        }

        $usuarios = $usuarios->get()
            ->filter(function ($user) {
                $rol = $this->normalize($user->rol);
                return in_array($rol, ['patrullero', 'guardia']);
            })
            ->sortBy([
                ['punto', 'asc'],
                ['name', 'asc']
            ]);

        $startDate = Carbon::parse($this->fechaInicio);
        $endDate = Carbon::parse($this->fechaFin);
        $fechas = [];
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
                        ->orWhereBetween('fecha_fin', [$this->fechaInicio, $this->fechaFin])
                        ->orWhere(function ($q) {
                            $q->where('fecha_inicio', '<', $this->fechaInicio)
                                ->where('fecha_fin', '>', $this->fechaFin);
                        });
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

        $horasExtrasPorUsuario = [];
        foreach ($usuarios as $user) {
            $registros = TiemposExtra::where('user_id', $user->id)
                ->whereBetween('fecha', [$this->fechaInicio, $this->fechaFin])
                ->get();

            $porDia = [];
            foreach ($registros as $r) {
                $dia = Carbon::parse($r->fecha)->format('Y-m-d');
                $horas = (int) Carbon::parse($r->total_horas)->format('H');
                $porDia[$dia] = ($porDia[$dia] ?? 0) + $horas;
            }
            $horasExtrasPorUsuario[$user->id] = $porDia;
        }

        // Cargar permisos especiales
        $permisosPorUsuario = [];
        $permisos = \App\Models\PermisoEspecial::whereBetween('fecha_inicio', [$this->fechaInicio, $this->fechaFin])
            ->orWhereBetween('fecha_fin', [$this->fechaInicio, $this->fechaFin])
            ->orWhere(function ($q) {
                $q->where('fecha_inicio', '<', $this->fechaInicio)
                  ->where('fecha_fin', '>', $this->fechaFin);
            })
            ->get();

        foreach ($permisos as $permiso) {
            $inicio = Carbon::parse($permiso->fecha_inicio);
            $fin = Carbon::parse($permiso->fecha_fin);
            for ($d = $inicio->copy(); $d->lte($fin); $d->addDay()) {
                $fecha = $d->format('Y-m-d');
                $permisosPorUsuario[$permiso->user_id][$fecha] = [
                    'tipo' => $permiso->tipo,
                    'con_goce' => (int) $permiso->con_goce === 1,
                ];
            }
        }

        // Cargar faltas justificadas
        $faltasJustificadas = [];
        $faltasJustificadasQuery = \App\Models\FaltaJustificada::whereIn('fecha', $fechas)
            ->where('tipo', 'justificada')
            ->get();

        foreach ($faltasJustificadasQuery as $falta) {
            $userId = $falta->user_id;
            $fecha = $falta->fecha->format('Y-m-d');
            $faltasJustificadas[$userId][$fecha] = true;
        }

        // Cargar retardos
        $retardosPorUsuario = [];
        $retardosQuery = Retardo::whereIn('fecha', $fechas)
            ->get();

        foreach ($retardosQuery as $retardo) {
            $userId = $retardo->user_id;
            $fecha = $retardo->fecha->format('Y-m-d');
            $minutos = $retardo->minutos_retardo;
            $retardosPorUsuario[$userId][$fecha] = $minutos;
        }

        // Cargar incapacidades
        $incapacidadesPorUsuario = [];
        $incapacidades = \App\Models\Incapacidad::where(function ($q) {
            $q->whereBetween('fecha_inicio', [$this->fechaInicio, $this->fechaFin]);
        })
        ->orWhere(function ($q) {
            $q->whereDate(\DB::raw('DATE_ADD(fecha_inicio, INTERVAL dias_incapacidad - 1 DAY)'), '>=', $this->fechaInicio)
              ->where('fecha_inicio', '<=', $this->fechaFin);
        })
        ->orWhere(function ($q) {
            $q->where('fecha_inicio', '<', $this->fechaInicio)
              ->whereDate(\DB::raw('DATE_ADD(fecha_inicio, INTERVAL dias_incapacidad - 1 DAY)'), '>', $this->fechaFin);
        })
        ->get();

        foreach ($incapacidades as $incapacidad) {
            $inicio = Carbon::parse($incapacidad->fecha_inicio);
            $fin = $inicio->copy()->addDays($incapacidad->dias_incapacidad - 1);

            for ($d = $inicio->copy(); $d->lte($fin); $d->addDay()) {
                $fecha = $d->format('Y-m-d');
                if (Carbon::parse($fecha)->between(Carbon::parse($this->fechaInicio), Carbon::parse($this->fechaFin))) {
                    $incapacidadesPorUsuario[$incapacidad->user_id][] = $fecha;
                }
            }
        }

        // Calcular nómina para cada usuario
        $nominaPorUsuario = [];
        foreach ($usuarios as $user) {
            try {
                $resultado = $this->calculoService->calcularPercepciones(
                    $user,
                    $this->fechaInicio,
                    $this->fechaFin,
                    [
                        'vacacionesPorUsuario' => $vacacionesPorUsuario,
                        'asistenciasIndexadas' => $asistenciasIndexadas,
                        'horasExtrasPorUsuario' => $horasExtrasPorUsuario,
                        'permisosPorUsuario' => $permisosPorUsuario,
                        'faltasJustificadas' => $faltasJustificadas,
                        'retardosPorUsuario' => $retardosPorUsuario,
                        'incapacidadesPorUsuario' => $incapacidadesPorUsuario,
                    ]
                );

                if ($resultado['success']) {
                    $nominaPorUsuario[$user->id] = $resultado;
                } else {
                    $nominaPorUsuario[$user->id] = [
                        'success' => false,
                        'error' => $resultado['error'] ?? 'Error desconocido',
                        'subtotal_percepciones' => 0,
                    ];
                }
            } catch (\Exception $e) {
                $nominaPorUsuario[$user->id] = [
                    'success' => false,
                    'error' => 'Excepción: ' . $e->getMessage(),
                    'subtotal_percepciones' => 0,
                ];
            }
        }

        return [
            'usuarios' => $usuarios,
            'fechas' => $fechas,
            'vacacionesPorUsuario' => $vacacionesPorUsuario,
            'asistenciasIndexadas' => $asistenciasIndexadas,
            'horasExtrasPorUsuario' => $horasExtrasPorUsuario,
            'permisosPorUsuario' => $permisosPorUsuario,
            'faltasJustificadas' => $faltasJustificadas,
            'retardosPorUsuario' => $retardosPorUsuario,
            'incapacidadesPorUsuario' => $incapacidadesPorUsuario,
            'nominaPorUsuario' => $nominaPorUsuario,
        ];
    }

    protected function getSubpuntosPorPunto()
    {
        $monterreyId = \App\Models\Punto::where('nombre', 'MONTERREY')->value('id');

        $codigos = [];
        if ($monterreyId) {
            $codigos = \App\Models\Subpunto::where('punto_id', $monterreyId)->pluck('codigo', 'nombre')->toArray();
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
            'GUANAJUATO' => [
                ['nombre' => 'SILAO', 'codigo' => null],
                ['nombre' => 'CELAYA', 'codigo' => null],
                ['nombre' => 'SALAMANCA', 'codigo' => null],
            ],
            'NUEVO LAREDO' => [
                ['nombre' => 'ZONA DE ABASTOS V', 'codigo' => null],
            ],
            'MEXICO' => [
                ['nombre' => 'VALLE DE MEXICO', 'codigo' => null],
            ],
            'SLP' => [
                ['nombre' => 'WATCO', 'codigo' => null],
                ['nombre' => 'BMW', 'codigo' => null],
                ['nombre' => 'ZONA DE ABASTOS I', 'codigo' => null],
                ['nombre' => 'INTERPUERTO Y TALLER', 'codigo' => null],
            ],
            'XALAPA' => [
                ['nombre' => 'XALAPA', 'codigo' => null],
            ],
            'MICHOACAN' => [
                ['nombre' => 'MICHOACÁN', 'codigo' => null],
            ],
            'PUEBLA' => [
                ['nombre' => 'PUEBLA', 'codigo' => null],
            ],
            'TOLUCA' => [
                ['nombre' => 'TOLUCA', 'codigo' => null],
            ],
            'QUERETARO' => [
                ['nombre' => 'QUERÉTARO', 'codigo' => null],
            ],
            'SALTILLO' => [
                ['nombre' => 'SALTILLO', 'codigo' => null],
            ],
            'DRONES' => [
                ['nombre' => 'DRONES', 'codigo' => null],
            ],
        ];
    }

    protected function normalize($string)
    {
        return strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $string));
    }
}
