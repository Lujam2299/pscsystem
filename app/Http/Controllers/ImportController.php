<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Unidades;
use App\Models\DocumentacionAltas;
use App\Models\SolicitudAlta;
use App\Models\Archivonomina;
use App\Models\SolicitudVacaciones;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Exception;
use Illuminate\Support\Facades\Hash;

class ImportController extends Controller
{
    public function importarExcel(Request $request)
    {
        DB::beginTransaction();

        try {

            if (!$request->hasFile('excel')) {
                Log::error('No se subió ningún archivo.');
                return back()->with('error', 'No se subió ningún archivo.');
            }

            $file = $request->file('excel');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet(0);
            $rows = $sheet->toArray();

            foreach (array_slice($rows, 3) as $row) {

                $nombre = ucwords(mb_strtolower(preg_replace('/\s+/', ' ', trim($row[3])), 'UTF-8'));
                $apellidoPaterno = ucwords(mb_strtolower(preg_replace('/\s+/', ' ', trim($row[4])), 'UTF-8'));
                $apellidoMaterno = ucwords(mb_strtolower(preg_replace('/\s+/', ' ', trim($row[5])), 'UTF-8'));

                $fechaNacimiento = Carbon::parse($row[6])->format('Y-m-d');
                $fechaIngreso = Carbon::now('America/Mexico_City')->format('Y-m-d');

                $solicitudAltaId = DB::table('solicitud_altas')->insertGetId([
                    'nss' => $row[0],
                    'rfc' => $row[1],
                    'curp' => $row[2],
                    'nombre' => $nombre,
                    'apellido_paterno' => $apellidoPaterno,
                    'apellido_materno' => $apellidoMaterno,
                    'fecha_nacimiento' => $fechaNacimiento,
                    'rol' => $row[7],
                    'empresa' => $row[8],
                    'email' => $row[9],
                    'status' => 'Aceptada',
                    'departamento' => $row[10],
                    'observaciones' => 'Solicitud Aceptada.'
                ]);

                $solDocsId = DB::table('documentacion_altas')->insertGetId([
                    'solicitud_id' => $solicitudAltaId,
                ]);

                DB::table('users')->insert([
                    'name' => $nombre.' '.$apellidoPaterno.' '.$apellidoMaterno,
                    'sol_alta_id' => $solicitudAltaId,
                    'sol_docs_id' => $solDocsId,
                    'email' => $row[9],
                    'password' => Hash::make($row[1]),
                    'fecha_ingreso' => $fechaIngreso,
                    'rol' => $row[7],
                    'empresa' => $row[8],
                    'estatus' => 'Activo'
                ]);
            }

            DB::commit();

            return back()->with('success', 'Importación completada.');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al importar el archivo: ' . $e->getMessage());
        }
    }

    public function importarArchivoRosa(Request $request)
{
    ini_set('max_execution_time', 300);
    DB::beginTransaction();

    $importados = 0;
    $fallidos = 0;

    Log::info('Inicio de importación de archivo rosa');

    try {
        $file = $request->file('excel');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet(0);
        $rows = $sheet->toArray();

        foreach (array_slice($rows, 1) as $index => $row) {
            try {
                $primeraCelda = strtoupper(trim($row[0] ?? ''));

                if (
                    $primeraCelda === '' ||
                    str_starts_with($primeraCelda, 'ALTAS') ||
                    str_contains($primeraCelda, 'SIN MOVIMIENTOS') ||
                    str_contains($primeraCelda, 'SIN ALTAS')
                ) {
                    Log::info("Fila $index omitida (vacía o encabezado)");
                    continue;
                }

                $punto = ucwords(mb_strtolower(preg_replace('/\s+/', ' ', trim($row[0])), 'UTF-8'));
                $nombre = ucwords(mb_strtolower(preg_replace('/\s+/', ' ', trim($row[1])), 'UTF-8'));
                $apellidoPaterno = ucwords(mb_strtolower(preg_replace('/\s+/', ' ', trim($row[2])), 'UTF-8'));
                $apellidoMaterno = ucwords(mb_strtolower(preg_replace('/\s+/', ' ', trim($row[3])), 'UTF-8'));
                $estadoCivil = ucwords(mb_strtolower(preg_replace('/\s+/', ' ', trim($row[6])), 'UTF-8'));

                $fechaNacimiento = Carbon::parse($row[5])->format('Y-m-d');
                $fechaIngreso = Carbon::parse($row[4])->format('Y-m-d');

                $infonavit = !empty($row[13]) ? $row[13] : 'N/A';
                $fonacot = !empty($row[17]) ? $row[17] : 'N/A';
                $reingreso = !empty($row[21]) ? 'SI' : 'NO';

                $email = null;
                $password = null;
                if (!empty($row[29]) && filter_var($row[29], FILTER_VALIDATE_EMAIL)) {
                    $email = $row[29];
                }

                if (empty($row[11]))
                    $password = Hash::make($row[10]);
                else
                    $password = Hash::make($row[11]);

                $solicitudAltaId = DB::table('solicitud_altas')->insertGetId([
                    'punto' => $punto,
                    'nombre' => $nombre,
                    'apellido_paterno' => $apellidoPaterno,
                    'apellido_materno' => $apellidoMaterno,
                    'fecha_nacimiento' => $fechaNacimiento,
                    'estado_civil' => $estadoCivil,
                    'curp' => $row[10],
                    'rfc' => $row[11],
                    'nss' => $row[12],
                    'infonavit' => $infonavit,
                    'fonacot' => $fonacot,
                    'telefono' => $row[20],
                    'reingreso' => $reingreso,
                    'sd' => $row[24],
                    'sdi' => $row[25],
                    'empresa' => 'PSC',
                    'email' => $email,
                    'status' => 'Aceptada',
                    'observaciones' => 'Solicitud Aceptada.'
                ]);

                $solDocsId = DB::table('documentacion_altas')->insertGetId([
                    'solicitud_id' => $solicitudAltaId,
                ]);

                DB::table('users')->insert([
                    'name' => $nombre . ' ' . $apellidoPaterno . ' ' . $apellidoMaterno,
                    'sol_alta_id' => $solicitudAltaId,
                    'sol_docs_id' => $solDocsId,
                    'email' => $email,
                    'password' => $password,
                    'fecha_ingreso' => $fechaIngreso,
                    'punto' => $row[0],
                    'empresa' => 'PSC',
                    'estatus' => 'Activo'
                ]);

                $importados++;
                Log::info("Fila $index importada correctamente: $nombre $apellidoPaterno $apellidoMaterno");

            } catch (\Throwable $e) {
                $fallidos++;
                Log::error("Error en fila $index: " . $e->getMessage(), ['fila' => $row]);
                continue;
            }
        }

        DB::commit();

        Log::info("Importación completada. Importados: $importados, Fallidos: $fallidos");

        return back()->with('success', "Importación completada. Importados: $importados, Fallidos: $fallidos.");

    } catch (Exception $e) {
        DB::rollBack();
        Log::error('Error general al importar archivo rosa: ' . $e->getMessage());
        return back()->with('error', 'Error al importar el archivo: ' . $e->getMessage());
    }
}


public function importarBajas(Request $request)
{
    ini_set('max_execution_time', 600);
    DB::beginTransaction();

    try {
        if (!$request->hasFile('excel')) {
            return back()->with('error', 'No se ha subido ningún archivo.');
        }

        $file = $request->file('excel');
        \Log::info("Iniciando importación del archivo: ".$file->getClientOriginalName());

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->toArray();

        $processed = 0;
        $skipped = 0;
        $fechaReferencia = Carbon::create(2023, 1, 1);

        foreach ($rows as $index => $row) {
            $fila = $index + 1;
            $nombreOriginal = trim($row[0] ?? '');

            if (empty($nombreOriginal) || str_starts_with(strtoupper($nombreOriginal), 'BAJAS') || str_contains(strtoupper($nombreOriginal), 'TOTAL')) {
                \Log::info("Fila {$fila} omitida (encabezado o vacía): {$nombreOriginal}");
                $skipped++;
                continue;
            }

            $partes = explode(' ', mb_convert_case($nombreOriginal, MB_CASE_TITLE, 'UTF-8'));

            if (count($partes) < 3) {
                \Log::warning("Nombre con estructura inesperada en fila {$fila}: {$nombreOriginal}");
                $skipped++;
                continue;
            }

            $apellidos = array_slice($partes, 0, 2);
            $nombres = array_slice($partes, 2);
            $nombreFormateado = implode(' ', $nombres).' '.implode(' ', $apellidos);


            try {
                $fechaIngreso = $row[2];
                $fechaBaja = $row[3];

                if (!$fechaIngreso || !$fechaBaja) {
                    \Log::warning("Fila {$fila} omitida - fechas inválidas: Ingreso=".($row[2] ?? 'NULL')." Baja=".($row[3] ?? 'NULL'));
                    $skipped++;
                    continue;
                }
            } catch (\Exception $e) {
                \Log::error("Error procesando fechas fila {$fila}: ".$e->getMessage());
                $skipped++;
                continue;
            }

            $descuento = isset($row[5]) ? floatval(str_replace(['$', ',', ' '], '', $row[5])) : 0;
            $nuevoRebaje = isset($row[6]) ? floatval(str_replace(['$', ',', ' '], '', $row[6])) : 0;

            $user = User::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($nombreFormateado))])
                      ->first();

            if (!$user) {
                \Log::warning("Usuario no encontrado - Fila {$fila}: {$nombreFormateado}");
                $skipped++;
                continue;
            }

            try {
                DB::table('solicitud_bajas')->insert([
                    'user_id' => $user->id,
                    'fecha_solicitud' => date('Y-m-d', strtotime($fechaBaja)),
                    'fecha_baja' => date('Y-m-d', strtotime($fechaBaja)),
                    'por' => trim($row[4] ?? ''),
                    'descuento' => $descuento,
                    'nuevorebajefiniquito' => $nuevoRebaje,
                    'estatus' => 'Aceptada',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $user->estatus = 'Inactivo';
                $user->save();

                $processed++;
                \Log::info("Registro insertado - Fila {$fila}: {$user->id} - {$nombreFormateado}");

            } catch (\Exception $e) {
                \Log::error("Error insertando fila {$fila}: ".$e->getMessage());
                $skipped++;
                continue;
            }
        }

        DB::commit();

        $message = "Importación completada. {$processed} registros procesados, {$skipped} filas omitidas.";
        \Log::info($message);

        return back()->with('success', $message);

    } catch (Exception $e) {
        DB::rollBack();
        \Log::error("Error general en importación: ".$e->getMessage());
        return back()->with('error', 'Error al importar: '.$e->getMessage());
    }
}

    public function importarUnidades(Request $request)
    {
        DB::beginTransaction();

        try {
            if (!$request->hasFile('excel')) {
                Log::error('No se subió ningún archivo.');
                return back()->with('error', 'No se subió ningún archivo.');
            }

            $file = $request->file('excel');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            foreach (array_slice($rows, 1) as $row) {
                if (empty($row[0])) continue;

                Unidades::create([
                    'nombre_propietario' => trim($row[0]),
                    'zona'               => trim($row[1]),
                    'marca'              => trim($row[2]),
                    'modelo'             => trim($row[3]),
                    'placas'             => trim($row[4]),
                    'kms'                => trim($row[5]),
                    'asignacion_punto'   => trim($row[6]),
                    'estado_vehiculo'    => trim($row[7]),
                    'observaciones'      => trim($row[8]),
                ]);
            }

            DB::commit();

            return back()->with('success', 'Unidades importadas correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al importar unidades: ' . $e->getMessage());
            return back()->with('error', 'Error al importar el archivo: ' . $e->getMessage());
        }
    }


     public function unifyDuplicates(Request $request)
    {
        // Opcional: poner un límite de tiempo
        ini_set('max_execution_time', 300);

        \Log::info('Iniciando unificación de duplicados desde la interfaz.');

        try {
            // Paso 1: Obtener nombres duplicados (insensible a mayúsculas)
            $duplicates = DB::table('users')
                ->select(DB::raw('LOWER(name) as lower_name'), DB::raw('COUNT(*) as count'))
                ->groupBy(DB::raw('LOWER(name)'))
                ->having('count', '>', 1)
                ->pluck('lower_name');

            if ($duplicates->isEmpty()) {
                return redirect()->back()->with('info', 'No se encontraron usuarios duplicados.');
            }

            $processed = 0;
            $logInfo = "<strong>Usuarios procesados:</strong><ul>";

            foreach ($duplicates as $lowerName) {
                $users = User::whereRaw('LOWER(name) = ?', [$lowerName])
                    ->orderBy('fecha_ingreso', 'asc')
                    ->get();

                if ($users->count() <= 1) continue;

                $latestUser = $users->last();
                $solicitudAlta = SolicitudAlta::find($latestUser->sol_alta_id);

                if (!$solicitudAlta) {
                    \Log::warning("SolicitudAlta no encontrada para sol_alta_id: {$latestUser->sol_alta_id}");
                    continue;
                }

                // Construir historial de reingresos
                $reingresos = $users->map(function ($user, $index) {
                    $fecha = Carbon::parse($user->fecha_ingreso)->format('d/m/Y');
                    return ($index + 1) . "° ingreso: {$fecha}";
                })->implode(', ');

                // Actualizar
                $solicitudAlta->reingreso = $reingresos;
                $solicitudAlta->save();

                // Eliminar antiguos
                $users->except($latestUser->id)->each(function ($user) {
                    \Log::info("Procesando usuario antiguo: {$user->id}, {$user->name}, ingreso: {$user->fecha_ingreso}");

                    // Marcar como Inactivo ANTES de eliminar
                    $user->estatus = 'Inactivo';
                    $user->save();
                    $user->delete();
                });

                $logInfo .= "<li><strong>{$users->first()->name}</strong>: {$users->count()} ingresos unificados</li>";
                $processed++;
            }

            $logInfo .= "</ul>";

            return redirect()->back()->with('success', "✅ Proceso completado. Se unificaron $processed usuarios.")
                             ->with('info', $logInfo);

        } catch (\Exception $e) {
            \Log::error('Error en unifyDuplicates: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

public function updateDestajos()
{
    ini_set('max_execution_time', 600);
    ini_set('memory_limit', '1024M');
    set_time_limit(600);

    $offset = request('offset', 0);
    $limit = 10; // Procesar de 10 en 10 para ser más conservador

    $registros = Archivonomina::whereNotNull('arch_destajo')
        ->where('arch_destajo', '!=', '')
        ->skip($offset)
        ->take($limit)
        ->get();

    $actualizados = 0;
    $totalProcesados = 0;

    foreach ($registros as $registro) {
        try {
            if (!Storage::disk('public')->exists($registro->arch_destajo)) {
                continue;
            }

            $filePath = Storage::disk('public')->path($registro->arch_destajo);

            if (!is_readable($filePath)) {
                continue;
            }

            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $this->encontrarHojaResumen($spreadsheet);

            if (!$worksheet) {
                continue;
            }

            $valorDestajo = $this->encontrarValorDestajo($worksheet);

            if ($valorDestajo !== null) {
                $registro->total_destajos = $valorDestajo;
                $registro->save();
                $actualizados++;
            }

            $totalProcesados++;

        } catch (\Exception $e) {
            \Log::error("Error registro {$registro->id}: " . $e->getMessage());
            continue;
        }
    }

    // Verificar si hay más registros por procesar
    $totalRegistros = Archivonomina::whereNotNull('arch_destajo')
        ->where('arch_destajo', '!=', '')
        ->count();

    $quedanMas = ($offset + $limit) < $totalRegistros;

    return response()->json([
        'success' => true,
        'message' => "Lote procesado ({$offset}-" . ($offset + $limit) . "). Actualizados: {$actualizados}/{$totalProcesados}",
        'actualizados' => $actualizados,
        'continuar' => $quedanMas,
        'siguiente_offset' => $offset + $limit,
        'total_registros' => $totalRegistros,
        'procesados_hasta_ahora' => $offset + $totalProcesados
    ]);
}

private function encontrarHojaResumen($spreadsheet)
{
    if ($spreadsheet->sheetNameExists('RESUMEN')) {
        return $spreadsheet->getSheetByName('RESUMEN');
    }

    $sheetCount = $spreadsheet->getSheetCount();
    if ($sheetCount > 0) {
        return $spreadsheet->getSheet($sheetCount - 1);
    }

    return null;
}

private function encontrarValorDestajo($worksheet)
{
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();

    \Log::info("Buscando en hoja. Filas: {$highestRow}, Columnas: {$highestColumn}");

    for ($row = 1; $row <= $highestRow; $row++) {
        for ($col = 'A'; $col <= $highestColumn && $col <= 'J'; $col++) {
            $cellCoordinate = $col . $row;
            $cell = $worksheet->getCell($cellCoordinate);
            $cellValue = $cell->getValue();

            if ($cellValue && $this->esTotalDestajo($cellValue)) {
                \Log::info("Encontrado 'TOTAL DESTAJO' en celda: {$cellCoordinate} con valor: " . trim($cellValue));

                // Obtener la celda a la derecha
                $colIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($col);
                $nextCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                $nextCellCoordinate = $nextCol . $row;

                \Log::info("Buscando valor en celda: {$nextCellCoordinate}");

                $nextCell = $worksheet->getCell($nextCellCoordinate);
                $valorCeldaDerecha = $nextCell->getValue();
                $valorCalculado = $nextCell->getCalculatedValue(); // Obtener el valor calculado
                $valorFormateado = $nextCell->getFormattedValue();

                \Log::info("Valor crudo: " . var_export($valorCeldaDerecha, true));
                \Log::info("Valor calculado: " . var_export($valorCalculado, true));
                \Log::info("Valor formateado: " . $valorFormateado);

                // Priorizar el valor calculado, luego el formateado
                $valorNumerico = null;

                if (is_numeric($valorCalculado)) {
                    $valorNumerico = (float) $valorCalculado;
                    \Log::info("Usando valor calculado: " . $valorNumerico);
                } else {
                    $valorNumerico = $this->extraerValorNumerico($valorFormateado);
                    \Log::info("Usando valor formateado procesado: " . var_export($valorNumerico, true));
                }

                if ($valorNumerico !== null) {
                    return $valorNumerico;
                } else {
                    \Log::info("No se pudo extraer valor numérico");
                }
            }
        }
    }

    return null;
}

private function esTotalDestajo($texto)
{
    if (!is_string($texto)) {
        return false;
    }

    $textoNormalizado = trim(strtoupper($texto));
    $esTotalDestajo = strpos($textoNormalizado, 'TOTAL DESTAJO') !== false;

    if ($esTotalDestajo) {
        \Log::info("Coincidencia encontrada: " . $textoNormalizado);
    }

    return $esTotalDestajo;
}

private function extraerValorNumerico($valor)
{
    if ($valor === null || $valor === '') {
        return null;
    }

    if (is_numeric($valor)) {
        return (float) $valor;
    }

    // Limpiar el texto para extraer números
    $valorString = (string) $valor;
    $valorLimpio = preg_replace('/[^\d.,]/', '', $valorString);

    if (empty($valorLimpio)) {
        return null;
    }

    \Log::debug("Valor limpio: " . $valorLimpio);

    // Manejar diferentes formatos de número
    if (strpos($valorLimpio, ',') !== false && strpos($valorLimpio, '.') !== false) {
        // Determinar cuál es el separador decimal
        $lastDot = strrpos($valorLimpio, '.');
        $lastComma = strrpos($valorLimpio, ',');

        if ($lastDot > $lastComma) {
            // Formato: 1,234.56 (punto como decimal)
            $valorLimpio = str_replace(',', '', $valorLimpio);
        } else {
            // Formato: 1.234,56 (coma como decimal)
            $valorLimpio = str_replace('.', '', $valorLimpio);
            $valorLimpio = str_replace(',', '.', $valorLimpio);
        }
    } elseif (strpos($valorLimpio, ',') !== false) {
        // Posiblemente formato europeo, verificar si tiene sentido como decimal
        $parts = explode(',', $valorLimpio);
        if (strlen(end($parts)) <= 2 && count($parts) > 1) {
            // Última parte tiene 2 o menos dígitos, probablemente decimal
            $valorLimpio = str_replace('.', '', $valorLimpio);
            $valorLimpio = str_replace(',', '.', $valorLimpio);
        } else {
            // Parte entera con comas como separadores de miles
            $valorLimpio = str_replace(',', '', $valorLimpio);
        }
    }

    return is_numeric($valorLimpio) ? (float) $valorLimpio : null;
}

    public function importarVacaciones(Request $request)
{
    \Log::info('=== INICIO DE IMPORTACIÓN DE VACACIONES (PhpSpreadsheet) ===');

    // Aumentar límites para archivos grandes
    set_time_limit(300); // 5 minutos
    ini_set('memory_limit', '512M');

    $request->validate([
        'excel' => 'required|file|mimes:xlsx,xls|max:10240',
    ]);

    if (!$request->hasFile('excel')) {
        \Log::error('❌ No se recibió ningún archivo en la solicitud.');
        return back()->with('error', 'No se seleccionó ningún archivo.');
    }

    try {
        \Log::info('📂 Cargando archivo con PhpSpreadsheet...');
        $inputFileName = $request->file('excel')->getPathname();
        $spreadsheet = IOFactory::load($inputFileName);
        $sheetNames = $spreadsheet->getSheetNames();
        \Log::info('✅ Archivo cargado. Total de hojas: ' . count($sheetNames));

        $hojasConfig = [
            0 => 2, // Hoja 1 → columna C (índice 2)
            1 => 3, // Hoja 2 → columna D (índice 3)
            2 => 1, // Hoja 3 → columna B (índice 1)
            5 => 1, // Hoja 6 → columna B (índice 1)
        ];

        $totalProcesados = 0;
        $totalIgnorados = 0;

        foreach ($hojasConfig as $indiceHoja => $colNombre) {
            \Log::info("▶️ Procesando hoja índice: {$indiceHoja} (columna nombre: {$colNombre})");

            if (!isset($sheetNames[$indiceHoja])) {
                \Log::warning("⚠️ Hoja índice {$indiceHoja} no existe. Saltando...");
                continue;
            }

            $sheet = $spreadsheet->getSheet($indiceHoja);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            \Log::info("📊 Hoja '{$sheetNames[$indiceHoja]}' - Filas: {$highestRow}, Columnas: {$highestColumn}");

            if ($highestRow < 2) {
                \Log::warning("⚠️ Hoja {$indiceHoja} no tiene datos suficientes. Saltando...");
                continue;
            }

            // 🔍 Buscar encabezados en filas 1-5
            $encabezadosFila = null;
            $headers = [];

            for ($filaEncabezado = 1; $filaEncabezado <= 5; $filaEncabezado++) {
                $posibleHeaders = $sheet->rangeToArray('A' . $filaEncabezado . ':' . $highestColumn . $filaEncabezado, null, true, false)[0] ?? [];

                $tieneDel = false;
                $tieneAl = false;

                foreach ($posibleHeaders as $header) {
                    if (is_string($header)) {
                        if (Str::lower($header) === 'del') $tieneDel = true;
                        if (Str::lower($header) === 'al') $tieneAl = true;
                    }
                }

                if ($tieneDel && $tieneAl) {
                    $encabezadosFila = $filaEncabezado;
                    $headers = $posibleHeaders;
                    \Log::info("✅ Encabezados encontrados en fila {$filaEncabezado}: " . json_encode($headers));
                    break;
                }
            }

            if ($encabezadosFila === null) {
                \Log::error("❌ No se encontraron encabezados 'Del' y 'Al' en las primeras 5 filas de la hoja {$indiceHoja}. Saltando hoja.");
                $totalIgnorados += max(0, $highestRow - 1);
                continue;
            }

            // Buscar índices de columnas clave
            $delIndex = null;
            $alIndex = null;
            $obsIndex = null;

            foreach ($headers as $index => $header) {
                if (is_string($header)) {
                    if (Str::lower($header) === 'del') $delIndex = $index;
                    if (Str::lower($header) === 'al') $alIndex = $index;
                    if (Str::lower($header) === 'observaciones') $obsIndex = $index;
                }
            }

            \Log::info("🔍 Columnas encontradas - Del: " . ($delIndex !== null ? $delIndex : 'NO') . ", Al: " . ($alIndex !== null ? $alIndex : 'NO') . ", Observaciones: " . ($obsIndex !== null ? $obsIndex : 'NO'));

            if ($delIndex === null || $alIndex === null) {
                \Log::error("❌ Columnas 'Del' o 'Al' NO ENCONTRADAS en hoja {$indiceHoja}. Saltando toda la hoja.");
                $filasDatos = max(0, $highestRow - $encabezadosFila);
                $totalIgnorados += $filasDatos;
                continue;
            }

            $inicioDatos = $encabezadosFila + 1;
            \Log::info("▶️ Iniciando procesamiento de datos desde la fila {$inicioDatos}");

            for ($row = $inicioDatos; $row <= $highestRow; $row++) {
                \Log::info("----- Procesando fila #{$row} en hoja {$indiceHoja} -----");

                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false)[0];

                // Saltar filas vacías
                if (empty(array_filter($rowData, fn($v) => !is_null($v) && $v !== '' && $v !== false))) {
                    \Log::info("⏹️ Fila {$row} vacía. Saltando.");
                    $totalIgnorados++;
                    continue;
                }

                // Obtener nombre desde columna configurada
                $nombreCompleto = $rowData[$colNombre] ?? null;
                if (!$nombreCompleto || !is_string($nombreCompleto) || trim($nombreCompleto) === '') {
                    \Log::warning("📛 Nombre no válido en fila {$row}: " . json_encode($rowData[$colNombre] ?? 'VACÍO'));
                    $totalIgnorados++;
                    continue;
                }

                $nombreCompleto = trim($nombreCompleto);

                // ✅ AJUSTE: Convertir "Apellidos Nombres" a "Nombres Apellidos"
                $nombreCompleto = $this->convertirApellidoNombreANombreApellido($nombreCompleto);
                \Log::info("👤 Nombre convertido: '{$nombreCompleto}'");

                \Log::info("👤 Buscando usuario por nombre: '{$nombreCompleto}'");

                // 🔍 BUSCAR USUARIO INTELIGENTEMENTE
                $user = $this->buscarUsuarioPorNombreExcel($nombreCompleto);
                if (!$user) {
                    \Log::warning("❌ Usuario NO ENCONTRADO para: '{$nombreCompleto}'");
                    $totalIgnorados++;
                    continue;
                }

                // ✅ PARTE CORREGIDA: CÁLCULO DEL ÚLTIMO AÑO LABORAL

                $fechaIngresoRaw = $rowData[$colNombre + 1] ?? null;
                if (!$fechaIngresoRaw) {
                    \Log::warning("📅 Fecha de ingreso faltante para: {$nombreCompleto}");
                    $totalIgnorados++;
                    continue;
                }

                $fechaIngreso = $this->parsearFechaConValidacion($fechaIngresoRaw, 'Fecha de ingreso');
                if (!$fechaIngreso) {
                    \Log::warning("❌ Fecha de ingreso inválida o no corregible: {$fechaIngresoRaw}");
                    $totalIgnorados++;
                    continue;
                }

                $hoy = Carbon::today();

                // ✅ CORRECCIÓN: Calcular último aniversario cumplido
                $ultimoAniversario = $fechaIngreso->copy();
                $ultimoAniversario->year = $hoy->year;

                if ($ultimoAniversario->lte($hoy)) {
                    $inicioUltimoAnio = $ultimoAniversario;
                } else {
                    $inicioUltimoAnio = $ultimoAniversario->copy()->subYear();
                }

                \Log::info("⏳ Último año laboral: del {$inicioUltimoAnio->toDateString()} al {$hoy->toDateString()}");

                // ✅ FIN DE CORRECCIÓN

                $fechaInicioRaw = $rowData[$delIndex] ?? null;
                $fechaFinRaw = $rowData[$alIndex] ?? null;

                // ✅ AJUSTE: Ignorar celdas que contengan solo '-'
                if (trim($fechaInicioRaw) === '-') {
                    \Log::info("⏭️ Celda 'Del' contiene solo '-', omitiendo fila #{$row}.");
                    $totalIgnorados++;
                    continue;
                }
                if (trim($fechaFinRaw) === '-') {
                    \Log::info("⏭️ Celda 'Al' contiene solo '-', omitiendo fila #{$row}.");
                    $totalIgnorados++;
                    continue;
                }

                if (!$fechaInicioRaw || !$fechaFinRaw) {
                    \Log::warning("❌ Fechas 'Del' o 'Al' vacías para: {$nombreCompleto}");
                    $totalIgnorados++;
                    continue;
                }

                $fechaInicio = $this->parsearFechaConValidacion($fechaInicioRaw, 'Fecha inicio solicitud');
                $fechaFin = $this->parsearFechaConValidacion($fechaFinRaw, 'Fecha fin solicitud');

                if (!$fechaInicio || !$fechaFin) {
                    \Log::warning("❌ Fechas de solicitud inválidas: inicio={$fechaInicioRaw}, fin={$fechaFinRaw}");
                    $totalIgnorados++;
                    continue;
                }

                \Log::info("🗓️ Solicitud: del {$fechaInicio->toDateString()} al {$fechaFin->toDateString()}");

                if ($fechaInicio->gt($fechaFin)) {
                    \Log::warning("❌ Fecha 'Del' mayor que 'Al' para: {$nombreCompleto}. Saltando.");
                    $totalIgnorados++;
                    continue;
                }

                // ✅ VALIDAR QUE LA SOLICITUD ESTÉ DENTRO DEL ÚLTIMO AÑO LABORAL
                if ($fechaInicio->lt($inicioUltimoAnio)) {
                    \Log::info("⏭️ Solicitud inicia ANTES del último año laboral ({$fechaInicio->toDateString()} < {$inicioUltimoAnio->toDateString()}). Ignorando.");
                    $totalIgnorados++;
                    continue;
                }

                if ($fechaFin->gt($hoy)) {
                    \Log::info("⏭️ Solicitud termina DESPUÉS de hoy ({$fechaFin->toDateString()} > {$hoy->toDateString()}). Ignorando.");
                    $totalIgnorados++;
                    continue;
                }

                $observaciones = $obsIndex !== null ? ($rowData[$obsIndex] ?? '') : '';
                \Log::info("📝 Observaciones: " . ($observaciones ?: '[vacío]'));

                $tipo = 'Disfrutadas';
                if (Str::contains(Str::lower($observaciones), 'pago')) {
                    $tipo = 'Pagadas';
                    \Log::info("🔖 Tipo detectado: PAGADAS");
                }

                $diasSolicitados = $fechaInicio->diffInDays($fechaFin) + 1;
                \Log::info("🔢 Días solicitados: {$diasSolicitados}");

                $aniosTrabajados = $fechaIngreso->diffInYears($hoy); // Solo para cálculo de días por derecho
                $diasPorDerecho = $this->calcularDiasPorDerecho($aniosTrabajados);
                \Log::info("⚖️ Días por derecho ({$aniosTrabajados} años): {$diasPorDerecho}");

                $diasYaUtilizados = (int) SolicitudVacaciones::where('user_id', $user->id)
                    ->where('estatus', 'Aceptada')
                    ->whereBetween('fecha_inicio', [$inicioUltimoAnio, $hoy])
                    ->sum(DB::raw('COALESCE(dias_solicitados, 0)'));

                \Log::info("📉 Días ya utilizados: {$diasYaUtilizados}");

                $diasDisponibles = max(0, $diasPorDerecho - $diasYaUtilizados);
                \Log::info("📈 Días disponibles: {$diasDisponibles}");

                $monto = $tipo === 'Pagadas' ? ($diasSolicitados * 100) : 0;
                if ($monto > 0) {
                    \Log::info("💰 Monto calculado: {$monto}");
                }

                SolicitudVacaciones::create([
                    'user_id' => $user->id,
                    'dias_por_derecho' => $diasPorDerecho,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'monto' => $monto,
                    'observaciones' => $observaciones,
                    'tipo' => $tipo,
                    'dias_ya_utlizados' => $diasYaUtilizados, // ← ¡Asegúrate que este nombre coincida con tu BD!
                    'dias_disponibles' => $diasDisponibles,
                    'dias_solicitados' => $diasSolicitados,
                    'estatus' => 'Aceptada',
                    'created_at' => now(),
                ]);

                \Log::info("✅ REGISTRO CREADO con éxito para: {$nombreCompleto}");
                $totalProcesados++;
            }
        }

        if ($totalProcesados === 0 && $totalIgnorados === 0) {
            \Log::warning("⚠️ No se procesó ninguna hoja válida.");
            return back()->with('warning', '⚠️ No se encontraron hojas válidas para procesar.');
        }

        \Log::info("=== RESUMEN FINAL ===");
        \Log::info("✅ Total procesados: {$totalProcesados}");
        \Log::info("❌ Total ignorados: {$totalIgnorados}");
        \Log::info("=== IMPORTACIÓN FINALIZADA ===");

        return back()->with([
            'success' => "✅ ¡Importación completada! {$totalProcesados} registros procesados, {$totalIgnorados} ignorados."
        ]);

    } catch (\Exception $e) {
        \Log::error('🚨 ERROR GENERAL: ' . $e->getMessage());
        \Log::error('Trace: ' . $e->getTraceAsString());
        return back()->with('error', '❌ Error crítico: ' . $e->getMessage());
    }
}

private function convertirApellidoNombreANombreApellido($nombreExcel)
{
    if (!is_string($nombreExcel)) {
        return $nombreExcel;
    }

    $nombreExcel = trim($nombreExcel);
    $palabras = array_filter(explode(' ', $nombreExcel), function($palabra) {
        return !empty($palabra);
    });

    if (count($palabras) < 2) {
        return $nombreExcel;
    }

    // Asumir que las primeras 2 palabras son apellidos (puede ajustarse si hay apellidos compuestos)
    $numApellidos = 2;
    $total = count($palabras);

    if ($total <= $numApellidos) {
        // Si hay menos palabras que apellidos, usar tal cual
        return $nombreExcel;
    }

    $apellidos = implode(' ', array_slice($palabras, 0, $numApellidos));
    $nombres = implode(' ', array_slice($palabras, $numApellidos));

    $resultado = trim("{$nombres} {$apellidos}");

    return $resultado;
}
    /**
     * Busca un usuario en la BD intentando normalizar nombres en formato "Apellido, Nombre"
     */
    private function buscarUsuarioPorNombreExcel($nombreExcel)
    {
        $nombreExcel = trim($nombreExcel);
        $nombreExcel = preg_replace('/\s+/', ' ', $nombreExcel); // normalizar espacios

        // Si tiene coma, invertir: "Apellido, Nombre" → "Nombre Apellido"
        if (strpos($nombreExcel, ',') !== false) {
            [$apellidos, $nombres] = array_map('trim', explode(',', $nombreExcel, 2));
            $nombreBusqueda = $nombres . ' ' . $apellidos;
        } else {
            $nombreBusqueda = $nombreExcel;
        }

        \Log::info("🔍 Normalizado para búsqueda: '{$nombreBusqueda}'");

        // Dividir en palabras significativas (mínimo 2 caracteres)
        $palabras = array_filter(explode(' ', $nombreBusqueda), fn($p) => strlen($p) >= 2);

        if (empty($palabras)) {
            \Log::warning("⚠️ Nombre sin palabras válidas: {$nombreExcel}");
            return null;
        }

        // Construir query: debe coincidir con TODAS las palabras
        $query = User::query();
        foreach ($palabras as $palabra) {
            $query->where('name', 'like', "%{$palabra}%");
        }

        $usuario = $query->first();

        if ($usuario) {
            \Log::info("✅ Coincidencia encontrada: {$usuario->name} (ID: {$usuario->id})");
        } else {
            \Log::warning("❌ Ningún usuario coincide con todas las palabras: " . implode(', ', $palabras));
        }

        return $usuario;
    }

    private function calcularDiasPorDerecho($antiguedad)
    {
        return match (true) {
            $antiguedad < 1 => 12,
            $antiguedad == 1 => 12,
            $antiguedad == 2 => 14,
            $antiguedad == 3 => 16,
            $antiguedad == 4 => 18,
            $antiguedad == 5 => 20,
            $antiguedad >= 6 && $antiguedad <= 10 => 22,
            $antiguedad >= 11 && $antiguedad <= 15 => 24,
            $antiguedad >= 16 && $antiguedad <= 20 => 26,
            $antiguedad >= 21 && $antiguedad <= 25 => 28,
            $antiguedad >= 26 && $antiguedad <= 30 => 30,
            $antiguedad > 30 => 32,
            default => 12,
        };
    }

    /**
 * Parsea y valida una fecha, corrigiendo errores comunes como años mal escritos o seriales de Excel.
 */
/**
 * Parsea y valida una fecha, corrigiendo errores comunes como años mal escritos o seriales de Excel.
 * Ahora maneja caracteres invisibles y fechas inválidas.
 */
private function parsearFechaConValidacion($fechaRaw, $contexto = 'fecha')
{
    if (!$fechaRaw) {
        \Log::warning("📅 {$contexto} está vacía.");
        return null;
    }

    // Si es numérico, probablemente sea un serial de Excel
    if (is_numeric($fechaRaw)) {
        try {
            // Convertir serial de Excel a fecha
            if ($fechaRaw > 59) {
                $fechaRaw -= 1; // Corrección por bug de Excel
            }
            $unixDate = ($fechaRaw - 25569) * 86400; // 25569 = días entre 1900-01-01 y 1970-01-01
            $fecha = Carbon::createFromTimestamp($unixDate)->startOfDay();
            \Log::info("📅 {$contexto} convertida desde serial Excel: {$fecha->toDateString()}");
            return $fecha;
        } catch (\Exception $e) {
            \Log::error("❌ Error al convertir serial Excel '{$fechaRaw}' para {$contexto}: " . $e->getMessage());
            return null;
        }
    }

    // Limpiar caracteres invisibles
    $fechaRaw = preg_replace('/[\x00-\x1F\x7F]/', '', $fechaRaw);
    $fechaRaw = trim($fechaRaw);

    \Log::info("📅 {$contexto} recibida para parsear: '{$fechaRaw}' (tipo: " . gettype($fechaRaw) . ")");

    // Corregir años mal escritos (5+ dígitos)
    if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{5,})$/', $fechaRaw, $matches)) {
        $dia = $matches[1];
        $mes = $matches[2];
        $anio = $matches[3];
        $anioCorregido = substr($anio, -4);
        $fechaCorregida = "{$dia}/{$mes}/{$anioCorregido}";
        \Log::warning("⚠️ {$contexto} corregida: '{$fechaRaw}' → '{$fechaCorregida}'");
        $fechaRaw = $fechaCorregida;
    }

    // Intentar parsear con formato específico d/m/Y
    $fecha = Carbon::createFromFormat('d/m/Y', $fechaRaw);

    if ($fecha && $fecha->format('d/m/Y') === $fechaRaw) {
        // Verificar rango razonable
        if ($fecha->year < 1900 || $fecha->year > 2100) {
            \Log::error("❌ Fecha fuera de rango válido ({$fecha->toDateString()}) para: {$fechaRaw}");
            return null;
        }
        // Verificar que la fecha sea válida (por ejemplo, 30/02/2025 no es válida)
        $dia = $fecha->day;
        $mes = $fecha->month;
        $anio = $fecha->year;
        if (!checkdate($mes, $dia, $anio)) {
            \Log::error("❌ Fecha inválida según checkdate ({$fechaRaw})");
            return null;
        }
        \Log::info("📅 {$contexto} parseada correctamente con formato d/m/Y: {$fecha->toDateString()}");
        return $fecha;
    }

    // Si falla, intentar con Carbon::parse() (último recurso)
    try {
        $fecha = Carbon::parse($fechaRaw);

        // Verificar rango razonable
        if ($fecha->year < 1900 || $fecha->year > 2100) {
            \Log::error("❌ Fecha fuera de rango válido ({$fecha->toDateString()}) para: {$fechaRaw}");
            return null;
        }

        \Log::info("📅 {$contexto} parseada con Carbon::parse(): {$fecha->toDateString()}");
        return $fecha;

    } catch (\Exception $e) {
        \Log::error("❌ Error al parsear {$contexto} '{$fechaRaw}': " . $e->getMessage());
        return null;
    }
}


public function importarPersonalActivo(Request $request)
{
    \Log::info('=== INICIO DE IMPORTACIÓN DE PERSONAL ACTIVO (CON NUEVOS CAMPOS Y SIN DUPLICADOS) ===');

    set_time_limit(300);
    ini_set('memory_limit', '512M');

    $request->validate([
        'excel' => 'required|file|mimes:xlsx,xls|max:10240',
    ]);

    if (!$request->hasFile('excel')) {
        return back()->with('error', 'No se seleccionó ningún archivo.');
    }

    try {
        $inputFileName = $request->file('excel')->getPathname();
        $spreadsheet = IOFactory::load($inputFileName);
        $sheet = $spreadsheet->getSheet(1);

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        \Log::info("📊 Hoja activa - Filas: {$highestRow}, Columnas: {$highestColumn}");

        // Contadores
        $creados = 0;
        $actualizados = 0;
        $ignorados = 0;
        $yaRegistrados = 0; // ← ¡Nuevo contador!
        $nombresEnExcel = [];

        // Listas para agrupar resultados
        $usuariosEnExcelYBd = collect(); // Usuarios en Excel Y en BD (activos)
        $usuariosEnExcelNoBd = collect(); // Usuarios en Excel pero no en BD
        $usuariosEnBdNoExcel = collect(); // Usuarios en BD pero no en Excel

        // Procesar desde la fila 2
        for ($row = 2; $row <= $highestRow; $row++) {
            \Log::info("----- Procesando fila #{$row} -----");

            // Leer todos los campos necesarios
            $registroPatronal = $sheet->getCell("A{$row}")->getValue();
            $departamento = $sheet->getCell("B{$row}")->getValue(); // punto
            $puesto = $sheet->getCell("C{$row}")->getValue();       // rol
            $tipoPeriodo = $sheet->getCell("D{$row}")->getValue();
            $codigoEmpleado = $sheet->getCell("E{$row}")->getValue();
            $nombreCompletoExcel = $sheet->getCell("F{$row}")->getValue();
            $rfc = $sheet->getCell("G{$row}")->getValue();
            $nss = $sheet->getCell("H{$row}")->getValue();
            $curp = $sheet->getCell("I{$row}")->getValue();
            $estadoEmpleado = $sheet->getCell("J{$row}")->getValue();
            $fechaIngresoRaw = $sheet->getCell("K{$row}")->getValue();
            $tipoCotizacion = $sheet->getCell("L{$row}")->getValue();
            $sbcFijo = $sheet->getCell("M{$row}")->getValue();
            $sbcVariable = $sheet->getCell("N{$row}")->getValue();
            $sbcTopado = $sheet->getCell("O{$row}")->getValue();
            $salarioDiario = $sheet->getCell("P{$row}")->getValue();

            // Validar nombre
            if (!$nombreCompletoExcel || !is_string($nombreCompletoExcel)) {
                \Log::warning("📛 Nombre no válido en fila {$row}");
                $ignorados++;
                continue;
            }

            if (trim(strtoupper($estadoEmpleado)) === 'BAJA') {
                \Log::info("⏭️ Usuario con estado 'Baja' en fila {$row}, omitiendo.");
                $ignorados++;
                continue;
            }

            // Validar NSS
            if (!$nss) {
                \Log::warning("🆔 NSS faltante en fila {$row}");
                $ignorados++;
                continue;
            }

            // Validar fecha de ingreso
            $fechaIngreso = $this->parsearFechaConValidacion($fechaIngresoRaw, 'Fecha de ingreso');
            if (!$fechaIngreso) {
                \Log::warning("📅 Fecha de ingreso inválida en fila {$row}: {$fechaIngresoRaw}");
                $ignorados++;
                continue;
            }

            // Normalizar nombre
            $nombreNormalizado = $this->normalizarNombreApellidos($nombreCompletoExcel);
            if (!$nombreNormalizado) {
                \Log::warning("📛 No se pudo normalizar nombre: {$nombreCompletoExcel}");
                $ignorados++;
                continue;
            }

            $nombresEnExcel[] = $nombreNormalizado;

            // 🔍 BUSCAR USUARIO: Primero coincidencia EXACTA (insensible a mayúsculas), luego LIKE
            $user = User::whereRaw('UPPER(name) = ?', [strtoupper($nombreNormalizado)])->first();

            if (!$user) {
                // Si no se encuentra, buscar con LIKE (máximo 1 resultado)
                $user = User::where('name', 'like', "%{$nombreNormalizado}%")
                    ->orderBy('id', 'asc')
                    ->first();
            }

            if ($user) {
                $yaRegistrados++;

                // ✅ AGREGAR A LA LISTA DE "EN EXCEL Y EN BD"
                $usuariosEnExcelYBd->push([
                    'nombre' => $nombreNormalizado,
                    'id_bd' => $user->id,
                    'estatus' => $user->estatus,
                    'num_empleado' => $user->num_empleado ?? 'N/A',
                    'empresa' => $user->empresa ?? 'N/A',
                ]);

                if ($user->estatus === 'Activo') {
                    \Log::info("✅ Usuario ya activo: {$nombreNormalizado} (ID: {$user->id})");
                    $ignorados++;
                } else {
                    $user->estatus = 'Activo';
                    $user->save();
                    \Log::info("🔄 Usuario reactivado: {$nombreNormalizado} (ID: {$user->id})");
                    $actualizados++;
                }
            } else {
                // ✅ AGREGAR A LA LISTA DE "EN EXCEL PERO NO EN BD"
                $usuariosEnExcelNoBd->push([
                    'nombre' => $nombreNormalizado,
                    'num_empleado' => $codigoEmpleado,
                    'empresa' => 'PSC',
                ]);

                // Crear en solicitud_altas con TODOS los nuevos campos
                $solicitudAlta = SolicitudAlta::create([
                    'nombre' => $nombreNormalizado,
                    'apellido_paterno' => $this->extraerApellidoPaterno($nombreCompletoExcel),
                    'apellido_materno' => $this->extraerApellidoMaterno($nombreCompletoExcel),
                    'nss' => $nss,
                    'curp' => $curp,
                    'rfc' => $rfc,
                    'fecha_ingreso' => $fechaIngreso,
                    'status' => 'Activo',
                    'observaciones' => 'Solicitud Aceptada',
                    'empresa' => 'PSC',
                    'registro_patronal' => $registroPatronal,
                    'punto' => $departamento,
                    'rol' => $puesto,
                    'tipo_periodo' => $tipoPeriodo,
                    'tipo_cotizacion' => $tipoCotizacion,
                    'sbc_fijo' => $sbcFijo,
                    'sbc_variable' => $sbcVariable,
                    'sbc_topado' => $sbcTopado,
                    'sd' => $salarioDiario,
                ]);

                \Log::info("📄 SolicitudAlta creada: ID {$solicitudAlta->id}");

                // Crear en documentacion_altas
                $docAlta = DocumentacionAltas::create([
                    'solicitud_id' => $solicitudAlta->id,
                ]);
                \Log::info("📄 DocumentacionAltas creada: ID {$docAlta->id}");

                // Crear en users
                $user = User::create([
                    'name' => $this->crearNombreCompletoParaUsers($nombreCompletoExcel),
                    'password' => bcrypt($nss),
                    'email' => $this->generarEmailTemporal($nombreNormalizado, $nss),
                    'estatus' => 'Activo',
                    'empresa' => 'PSC',
                    'num_empleado' => $codigoEmpleado,
                    'fecha_ingreso' => $fechaIngreso,
                    'sol_alta_id' => $solicitudAlta->id,
                    'sol_docs_id' => $docAlta->id,
                    'punto' => $departamento,
                    'rol' => $puesto,
                    'created_at' => now(),
                ]);

                \Log::info("✅ Usuario creado: {$nombreNormalizado} (ID: {$user->id})");
                $creados++;
            }
        }

        // 🔍 COMPROBACIÓN FINAL: 3 listas claras
        \Log::info("🔍 Iniciando comprobación final...");

        // 1. Usuarios en BD pero NO en Excel
        $usuariosEnBdNoExcel = User::where('estatus', 'Activo')
            ->whereNull('deleted_at')
            ->whereNotIn(DB::raw('UPPER(name)'), array_map('strtoupper', $nombresEnExcel))
            ->where('empresa', '!=', 'Montana')
            ->whereNotNull('empresa')
            ->select('name', 'id', 'num_empleado', 'empresa')
            ->get();

        // Mostrar resumen
        \Log::info("📊 Usuarios en Excel Y en BD (activos): {$usuariosEnExcelYBd->count()}");
        \Log::info("📊 Usuarios en Excel pero NO en BD: {$usuariosEnExcelNoBd->count()}");
        \Log::info("📊 Usuarios en BD pero NO en Excel: {$usuariosEnBdNoExcel->count()}");

        \Log::info("=== RESUMEN FINAL ===");
        \Log::info("✅ Nuevos creados: {$creados}");
        \Log::info("🔄 Actualizados (reactivados): {$actualizados}");
        \Log::info("📊 Ya registrados: {$yaRegistrados}");
        \Log::info("⏹️ Ignorados (ya activos): {$ignorados}");
        \Log::info("=== IMPORTACIÓN FINALIZADA ===");

        return back()->with([
            'success' => "✅ ¡Importación completada! Nuevos: {$creados}, Actualizados: {$actualizados}, Ya registrados: {$yaRegistrados}, Ignorados: {$ignorados}.",
            'resumen' => [
                'en_excel_y_bd' => $usuariosEnExcelYBd,
                'en_excel_no_bd' => $usuariosEnExcelNoBd,
                'en_bd_no_excel' => $usuariosEnBdNoExcel,
            ],
        ]);

    } catch (\Exception $e) {
        \Log::error('🚨 ERROR GENERAL: ' . $e->getMessage());
        \Log::error('Trace: ' . $e->getTraceAsString());
        return back()->with('error', '❌ Error crítico: ' . $e->getMessage());
    }
}
/**
 * Normaliza nombre: detecta apellidos compuestos y separa inteligentemente
 */
private function normalizarNombreApellidos($nombreExcel)
{
    if (!is_string($nombreExcel)) {
        \Log::warning("❌ Nombre no es string: " . json_encode($nombreExcel));
        return null;
    }

    // ✅ Normalizar espacios: eliminar múltiples, al inicio y final
    $nombreExcel = preg_replace('/\s+/', ' ', trim($nombreExcel));
    \Log::info("🧹 Nombre limpio de espacios: '{$nombreExcel}'");

    $palabras = array_filter(explode(' ', $nombreExcel), function($palabra) {
        return !empty($palabra); // ✅ Filtrar elementos vacíos
    });

    if (count($palabras) < 2) {
        \Log::warning("⚠️ Nombre demasiado corto después de limpiar: '{$nombreExcel}'. Usando tal cual.");
        return $nombreExcel;
    }

    // Estrategia: detectar apellidos compuestos
    $apellidos = [];
    $i = 0;
    $total = count($palabras);

    // Tomar al menos 1 palabra como apellido
    $apellidos[] = $palabras[$i++];

    // Si hay más palabras, ver si la siguiente forma parte de un apellido compuesto
    if ($i < $total) {
        $primera = strtoupper($palabras[0]);
        $segunda = strtoupper($palabras[1]);

        // Caso: "DE LA VEGA" → tomar 3 palabras
        if ($primera === 'DE' && $segunda === 'LA' && $i + 1 < $total) {
            $apellidos[] = $palabras[$i++];
            $apellidos[] = $palabras[$i++];
        }
        // Caso: "DE LOS SANTOS" → tomar 3 palabras
        elseif ($primera === 'DE' && $segunda === 'LOS' && $i + 1 < $total) {
            $apellidos[] = $palabras[$i++];
            $apellidos[] = $palabras[$i++];
        }
        // Caso: "VON BRAUN" → tomar 2 palabras
        elseif (in_array($primera, ['VON', 'VAN', 'MC', 'MAC', 'O']) && $i < $total) {
            $apellidos[] = $palabras[$i++];
        }
        // Caso: "DEL CASTILLO" → tomar 2 palabras
        elseif ($primera === 'DEL' && $i < $total) {
            $apellidos[] = $palabras[$i++];
        }
        // Caso normal: tomar 2 palabras como apellidos (si hay al menos 3 palabras en total)
        elseif ($total >= 3) {
            $apellidos[] = $palabras[$i++];
        }
        // Si solo hay 2 palabras, asumir que la primera es apellido, la segunda es nombre
        else {
            // Ya tomamos la primera palabra como apellido, no tomamos más
        }
    }

    // El resto son nombres
    $nombres = array_slice($palabras, count($apellidos));

    $apellidosStr = implode(' ', $apellidos);
    $nombresStr = implode(' ', $nombres);

    // ✅ Devolver solo los nombres
    $resultado = trim($nombresStr);

    \Log::info("🔤 Normalizado: '{$nombreExcel}' → Nombres: '{$resultado}', Apellidos: '{$apellidosStr}'");

    return $resultado;
}

/**
 * Extrae apellido paterno (inteligente)
 */
private function extraerApellidoPaterno($nombreExcel)
{
    if (!is_string($nombreExcel)) {
        return '';
    }

    $nombreExcel = preg_replace('/\s+/', ' ', trim($nombreExcel));
    $palabras = array_filter(explode(' ', $nombreExcel), 'strlen');
    if (empty($palabras)) return '';

    $primera = strtoupper($palabras[0]);

    // Caso: "DE LA VEGA" → apellido paterno = "DE LA VEGA"
    if ($primera === 'DE' && isset($palabras[1]) && strtoupper($palabras[1]) === 'LA' && isset($palabras[2])) {
        return implode(' ', array_slice($palabras, 0, 3));
    }
    // Caso: "DE LOS SANTOS" → apellido paterno = "DE LOS SANTOS"
    if ($primera === 'DE' && isset($palabras[1]) && strtoupper($palabras[1]) === 'LOS' && isset($palabras[2])) {
        return implode(' ', array_slice($palabras, 0, 3));
    }
    // Caso: "VON BRAUN" → apellido paterno = "VON BRAUN"
    if (in_array($primera, ['VON', 'VAN', 'MC', 'MAC', 'O']) && isset($palabras[1])) {
        return "{$palabras[0]} {$palabras[1]}";
    }
    // Caso: "DEL CASTILLO" → apellido paterno = "DEL CASTILLO"
    if ($primera === 'DEL' && isset($palabras[1])) {
        return "{$palabras[0]} {$palabras[1]}";
    }
    // Caso normal: primera palabra
    return $palabras[0];
}

/**
 * Extrae apellido materno (inteligente)
 */
private function extraerApellidoMaterno($nombreExcel)
{
    if (!is_string($nombreExcel)) {
        return null;
    }

    $nombreExcel = preg_replace('/\s+/', ' ', trim($nombreExcel));
    $palabras = array_filter(explode(' ', $nombreExcel), 'strlen');
    $total = count($palabras);

    if ($total < 2) {
        return null;
    }

    $primera = strtoupper($palabras[0]);

    // Si el primer apellido es compuesto, el materno empieza después
    if ($primera === 'DE' && isset($palabras[1]) && in_array(strtoupper($palabras[1]), ['LA', 'LOS']) && isset($palabras[2])) {
        // "DE LA VEGA MARTINEZ" → materno = "MARTINEZ"
        return $palabras[3] ?? null;
    }
    if (in_array($primera, ['VON', 'VAN', 'MC', 'MAC', 'O', 'DEL']) && isset($palabras[1])) {
        // "VON BRAUN STELLA" → materno = "STELLA"
        return $palabras[2] ?? null;
    }
    // Caso normal: segunda palabra
    return $palabras[1] ?? null;
}

/**
 * Genera email temporal
 */
private function generarEmailTemporal($nombre, $nss)
{
    $base = Str::slug($nombre, '.') . '.' . Str::slug($nss);
    return substr($base, 0, 100) . '@temporal.com';
}
private function crearNombreCompletoParaUsers($nombreExcel)
{
    if (!is_string($nombreExcel)) {
        return null;
    }

    // Normalizar espacios
    $nombreExcel = preg_replace('/\s+/', ' ', trim($nombreExcel));

    $palabras = array_filter(explode(' ', $nombreExcel), function($palabra) {
        return !empty($palabra);
    });

    if (count($palabras) < 2) {
        return $nombreExcel;
    }

    // Estrategia: detectar apellidos compuestos
    $apellidos = [];
    $i = 0;
    $total = count($palabras);

    // Tomar al menos 1 palabra como apellido
    $apellidos[] = $palabras[$i++];

    // Si hay más palabras, ver si la siguiente forma parte de un apellido compuesto
    if ($i < $total) {
        $primera = strtoupper($palabras[0]);
        $segunda = strtoupper($palabras[1]);

        // Caso: "DE LA VEGA" → tomar 3 palabras
        if ($primera === 'DE' && $segunda === 'LA' && $i + 1 < $total) {
            $apellidos[] = $palabras[$i++];
            $apellidos[] = $palabras[$i++];
        }
        // Caso: "DE LOS SANTOS" → tomar 3 palabras
        elseif ($primera === 'DE' && $segunda === 'LOS' && $i + 1 < $total) {
            $apellidos[] = $palabras[$i++];
            $apellidos[] = $palabras[$i++];
        }
        // Caso: "VON BRAUN" → tomar 2 palabras
        elseif (in_array($primera, ['VON', 'VAN', 'MC', 'MAC', 'O']) && $i < $total) {
            $apellidos[] = $palabras[$i++];
        }
        // Caso: "DEL CASTILLO" → tomar 2 palabras
        elseif ($primera === 'DEL' && $i < $total) {
            $apellidos[] = $palabras[$i++];
        }
        // Caso normal: tomar 2 palabras como apellidos (si hay al menos 3 palabras en total)
        elseif ($total >= 3) {
            $apellidos[] = $palabras[$i++];
        }
        // Si solo hay 2 palabras, asumir que la primera es apellido, la segunda es nombre
        else {
            // Ya tomamos la primera palabra como apellido, no tomamos más
        }
    }

    // El resto son nombres
    $nombres = array_slice($palabras, count($apellidos));

    $apellidosStr = implode(' ', $apellidos);
    $nombresStr = implode(' ', $nombres);

    $resultado = trim("{$nombresStr} {$apellidosStr}");

    return $resultado;
}

}
