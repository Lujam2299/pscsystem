<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Misiones;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Geocoder\Laravel\Facades\Geocoder;
use App\Models\Geofence;

class CustodiosController extends Controller
{
    public function misionesIndex(){
        $hoy = Carbon::now();
        $misiones = Misiones::where('fecha_inicio', '<=', $hoy)
                    ->where('fecha_fin', '>=', $hoy)
                    ->get();

        return view('custodios.misionesActuales', compact('misiones'));
    }

    public function custodiosIndex()
{
    $agentes = User::query()
        ->with(['solicitudAlta', 'documentacionAltas'])
        ->where('estatus', 'Activo')
        ->whereRaw("LOWER(users.rol) LIKE ?", ['%escolta%'])
        // 🔹 JOIN para ordenar por apellidos (FK correcta: sol_alta_id)
        ->leftJoin('solicitud_altas', 'users.sol_alta_id', '=', 'solicitud_altas.id')
        ->select('users.*')
        // 🔹 Ordenamiento: alfabético por apellido paterno → materno → nombre
        ->orderBy('solicitud_altas.apellido_paterno', 'asc')
        ->orderBy('solicitud_altas.apellido_materno', 'asc')
        ->orderBy('solicitud_altas.nombre', 'asc')
        ->paginate(10);

    return view('custodios.listaCustodios', compact('agentes'));
}

    public function nuevaMisionForm(){
        return view('custodios.nuevaMisionForm');
    }

    public function obtenerAgentesDisponibles(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $misiones = Misiones::whereBetween('fecha_fin', [$fechaInicio, $fechaFin])->get();

        $ocupados = collect();
        foreach ($misiones as $mision) {
            $ids = json_decode($mision->agentes_id, true) ?? [];
            $ocupados = $ocupados->merge($ids);
        }

        $ocupados = $ocupados->unique();
        $agentes = User::where('estatus', 'Activo')
            ->whereRaw("LOWER(rol) LIKE ?", ['%escolta%'])
            ->get();

        $agentesDisponibles = $agentes->map(function ($agente) use ($ocupados) {
            return [
                'id' => $agente->id,
                'name' => $agente->name,
                'ocupado' => $ocupados->contains($agente->id)
            ];
        });

        return response()->json($agentesDisponibles);
    }

    public function guardarMision(Request $request)
{
    $request->validate([
        'agentes_id' => 'required|array|min:1',
        'agentes_id.*' => 'exists:users,id',
        'tipo_servicio' => 'nullable|string|max:255',
        'ubicaciones' => 'required|array|min:1',
        'ubicaciones.*.direccion' => 'nullable|string|max:500',
        'ubicaciones.*.latitud' => 'nullable|numeric',
        'ubicaciones.*.longitud' => 'nullable|numeric',
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        'cliente' => 'nullable|string|max:255',
        'num_vehiculos' => 'nullable|integer|min:0',
        'tipo_vehiculos' => 'nullable|array',
        'tipo_vehiculos.*' => 'string|max:255',
        'armados' => 'nullable|string|in:armado,desarmado',

        // ✅ Cambiado: de 'hotel' → 'hoteles' (y es array)
        'hoteles' => 'nullable|array',
        'hoteles.*.nombre' => 'nullable|string|max:255',
        'hoteles.*.latitud' => 'nullable|numeric',
        'hoteles.*.longitud' => 'nullable|numeric',

        // ✅ Cambiado: de 'aeropuerto' → 'aeropuertos'
        'aeropuertos' => 'nullable|array',
        'aeropuertos.*.nombre' => 'nullable|string|max:255',
        'aeropuertos.*.latitud' => 'nullable|numeric',
        'aeropuertos.*.longitud' => 'nullable|numeric',

        // Vuelos: siguen igual (estructura anidada, no array)
        'vuelo_llegada.fecha' => 'nullable|date',
        'vuelo_llegada.flight' => 'nullable|string|max:50',
        'vuelo_llegada.hora' => 'nullable|date_format:H:i',

        'vuelo_salida.fecha' => 'nullable|date',
        'vuelo_salida.flight' => 'nullable|string|max:50',
        'vuelo_salida.hora' => 'nullable|date_format:H:i',
    ]);

    // === Procesamiento de ubicaciones (sin cambios) ===
    $ubicacionesProcesadas = [];
    foreach ($request->ubicaciones as $index => $ubicacion) {
        $direccion = $ubicacion['direccion'];
        $lat = $ubicacion['latitud'] ?? null;
        $lng = $ubicacion['longitud'] ?? null;

        if ($direccion && (!$lat || !$lng)) {
            try {
                $resultados = Geocoder::geocode($direccion)->get();
                if ($resultados->count() > 0) {
                    $resultado = $resultados->first();
                    $lat = $resultado->getCoordinates()->getLatitude();
                    $lng = $resultado->getCoordinates()->getLongitude();
                }
            } catch (\Exception $e) {
                // Opcional: manejo de error
            }
        }

        $ubicacionesProcesadas[] = compact('direccion', 'lat', 'lng');
    }

    try {
        $mision = Misiones::create([
            'agentes_id' => json_encode($request->agentes_id ?? []),
            'tipo_servicio' => $request->tipo_servicio,
            'ubicacion' => $ubicacionesProcesadas,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'cliente' => $request->cliente,
            'num_vehiculos' => $request->num_vehiculos,
            'tipo_vehiculos' => json_encode($request->tipo_vehiculos ?? []),
            'armados' => $request->armados,

            'datos_hotel' => json_encode($request->input('hoteles', [])),
            'datos_aeropuerto' => json_encode($request->input('aeropuertos', [])),

            // ✅ CAMBIO CRÍTICO AQUÍ
            'datos_vuelo' => json_encode([
                'llegada' => $request->input('vuelo_llegada', []),
                'salida' => $request->input('vuelo_salida', []),
            ]),

            'estatus' => 'Pendiente',
        ]);
    } catch (\Exception $e) {
        Log::error('Error al guardar misión:', [
            'message' => $e->getMessage(),
        ]);
        return back()->withInput()->with('error', 'Error al guardar la misión.');
    }

    // === Guardar múltiples geofences ===
    foreach ($request->input('hoteles', []) as $hotel) {
        if (!empty($hotel['nombre']) && isset($hotel['latitud']) && isset($hotel['longitud'])) {
            Geofence::create([
                'mision_id' => $mision->id,
                'tipo' => 'hotel',
                'centro' => [
                    'lat' => $hotel['latitud'],
                    'lng' => $hotel['longitud'],
                ],
                'radio_km' => 1.000,
                'nombre_referencia' => $hotel['nombre'],
            ]);
        }
    }

    foreach ($request->input('aeropuertos', []) as $aeropuerto) {
        if (!empty($aeropuerto['nombre']) && isset($aeropuerto['latitud']) && isset($aeropuerto['longitud'])) {
            Geofence::create([
                'mision_id' => $mision->id,
                'tipo' => 'aeropuerto',
                'centro' => [
                    'lat' => $aeropuerto['latitud'],
                    'lng' => $aeropuerto['longitud'],
                ],
                'radio_km' => 4.000,
                'nombre_referencia' => $aeropuerto['nombre'],
            ]);
        }
    }

    Log::info('Misión registrada exitosamente', ['id' => $mision->id]);

    return redirect()->route('dashboard')->with('success', 'Misión registrada exitosamente.');
}
    public function historialMisiones(){
        $misiones = Misiones::paginate(10);
        return view('custodios.historialMisiones', compact('misiones'));
    }

    public function misionesTerminadas(){
        $misiones = Misiones::where('estatus', 'Terminada')
            ->where('fecha_fin', '<', Carbon::now())
            ->paginate(10);
        return view('custodios.misionesTerminadas', compact('misiones'));
    }

    public function mensajesIndex(){
        return view('custodios.mensajes');
    }

    public function mostrarMapaGeocercas()
    {
        return view('custodios.mapaGeocercas');
    }

    public function verDetalleMision($misionId)
    {
        try {
            // Buscar la misión por ID
            $mision = Misiones::findOrFail($misionId);

            // Cargar las geocercas asociadas a esta misión
            $geocercas = Geofence::where('mision_id', $misionId)->get();

            // Opcional: Cargar agentes si necesitas mostrarlos
            $agentesIds = json_decode($mision->agentes_id, true);
            $agentes = User::whereIn('id', $agentesIds)->get();

            // Retornar la vista con los datos
            return view('custodios.detalle-mision', [
                'mision' => $mision,
                'geocercas' => $geocercas,
                'agentes' => $agentes ?? null, // Pasa agentes si los usas
            ]);

        } catch (\Exception $e) {
            Log::error("Error al cargar detalle de misión: ", ['error' => $e->getMessage()]);
            abort(404); // O redirige a una página de error
        }
    }

    public function edit($id)
{
    $mision = Misiones::findOrFail($id);

    // Decodificar campos existentes
    $mision->agentes_id = is_string($mision->agentes_id) ? json_decode($mision->agentes_id, true) : $mision->agentes_id;
    $mision->ubicacion = is_string($mision->ubicacion) ? json_decode($mision->ubicacion, true) : $mision->ubicacion;
    $mision->datos_hotel = is_string($mision->datos_hotel) ? json_decode($mision->datos_hotel, true) : $mision->datos_hotel;
    $mision->datos_aeropuerto = is_string($mision->datos_aeropuerto) ? json_decode($mision->datos_aeropuerto, true) : $mision->datos_aeropuerto;
    $mision->tipo_vehiculos = is_string($mision->tipo_vehiculos) ? json_decode($mision->tipo_vehiculos, true) : $mision->tipo_vehiculos;

    // ✅ NUEVO: Manejo robusto de datos_vuelo
    // 1. Verificar si es una cadena
    if (is_string($mision->datos_vuelo)) {
        // 2. Intentar decodificarla
        $decodedVuelo = json_decode($mision->datos_vuelo, true);
        // 3. Verificar si la decodificación fue exitosa y devolvió un array
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedVuelo)) {
            // 4. Asignar los sub-arrays llegada/salida
            $mision->datos_vuelo_llegada = $decodedVuelo['llegada'] ?? [];
            $mision->datos_vuelo_salida = $decodedVuelo['salida'] ?? [];
        } else {
            // Si falla la decodificación, asignar arrays vacíos o un valor por defecto
            \Log::warning("json_decode failed for datos_vuelo in mission ID {$mision->id}. Error: " . json_last_error_msg());
            $mision->datos_vuelo_llegada = [];
            $mision->datos_vuelo_salida = [];
        }
    } elseif (is_array($mision->datos_vuelo)) {
        // 5. Si ya es un array (posible error previo o inconsistencia), usarlo directamente
        \Log::warning("datos_vuelo is already an array in mission ID {$mision->id}. This might indicate a data inconsistency.");
        // Extraer llegada y salida del array directamente
        $mision->datos_vuelo_llegada = $mision->datos_vuelo['llegada'] ?? [];
        $mision->datos_vuelo_salida = $mision->datos_vuelo['salida'] ?? [];
    } else {
        // 6. Si no es ni string ni array, asignar arrays vacíos
        \Log::warning("datos_vuelo is neither a string nor an array in mission ID {$mision->id}. Value: " . var_export($mision->datos_vuelo, true));
        $mision->datos_vuelo_llegada = [];
        $mision->datos_vuelo_salida = [];
    }

    $agentesDisponibles = User::whereRaw('LOWER(rol) LIKE ?', ['%escolta%'])
                        ->where('estatus', 'Activo')
                        ->get();

    return view('custodios.editar-mision', [ // Asegúrate de que la ruta sea correcta
        'mision' => $mision,
        'agentesDisponibles' => $agentesDisponibles,
    ]);
}

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
public function update(Request $request, $id)
{
    $request->validate([
        'agentes_id' => 'required|array|min:1',
        'agentes_id.*' => 'exists:users,id',
        // 'nivel_amenaza' => 'nullable|string|max:255',
        'tipo_servicio' => 'required|string|max:255',
        'ubicaciones' => 'required|array|min:1',
        'ubicaciones.*.direccion' => 'nullable|string|max:500',
        'ubicaciones.*.latitud' => 'nullable|numeric',
        'ubicaciones.*.longitud' => 'nullable|numeric',
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        'cliente' => 'nullable|string|max:255',
        // 'nombre_clave' => 'nullable|string|max:255',
        // 'pasajeros' => 'nullable|string|max:255',
        // 'tipo_operacion' => 'nullable|string|max:255',
        'num_vehiculos' => 'nullable|integer|min:0',
        'tipo_vehiculos' => 'nullable|array',
        'tipo_vehiculos.*' => 'string|max:255',
        'armados' => 'nullable|string|in:armado,desarmado',

        // ✅ Cambiado: de 'hotel.nombre' → 'hoteles.*.nombre' (y es array)
        'hoteles' => 'nullable|array',
        'hoteles.*.nombre' => 'nullable|string|max:255',
        'hoteles.*.latitud' => 'nullable|numeric',
        'hoteles.*.longitud' => 'nullable|numeric',

        // ✅ Cambiado: de 'aeropuerto.nombre' → 'aeropuertos.*.nombre'
        'aeropuertos' => 'nullable|array',
        'aeropuertos.*.nombre' => 'nullable|string|max:255',
        'aeropuertos.*.latitud' => 'nullable|numeric',
        'aeropuertos.*.longitud' => 'nullable|numeric',

        // Vuelos: siguen igual (estructura anidada, no array)
        'vuelo_llegada.fecha' => 'nullable|date',
        'vuelo_llegada.flight' => 'nullable|string|max:50',
        'vuelo_llegada.hora' => 'nullable|date_format:H:i',

        'vuelo_salida.fecha' => 'nullable|date',
        'vuelo_salida.flight' => 'nullable|string|max:50',
        'vuelo_salida.hora' => 'nullable|date_format:H:i',
    ]);

    $mision = Misiones::findOrFail($id);

    $ubicacionesProcesadas = [];
    foreach ($request->ubicaciones as $index => $ubicacion) {
        $direccion = $ubicacion['direccion'];
        $lat = $ubicacion['latitud'] ?? null;
        $lng = $ubicacion['longitud'] ?? null;

        if ($direccion && (!$lat || !$lng)) {
            Log::info("Geocodificando dirección #$index", ['direccion' => $direccion]);

            try {
                $resultados = Geocoder::geocode($direccion)->get();
                if ($resultados->count() > 0) {
                    $resultado = $resultados->first();
                    $lat = $resultado->getCoordinates()->getLatitude();
                    $lng = $resultado->getCoordinates()->getLongitude();
                    Log::info("Coordenadas #$index obtenidas via Geocoder", ['lat' => $lat, 'lng' => $lng]);
                } else {
                    Log::warning("No se encontraron resultados de geocodificación para la dirección #$index", ['direccion' => $direccion]);
                }
            } catch (\Exception $e) {
                Log::error("Error geocodificando dirección #$index", [
                    'direccion' => $direccion,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $ubicacionesProcesadas[] = [
            'direccion' => $direccion,
            'latitud' => $lat,
            'longitud' => $lng,
        ];
    }

    try {
        $mision->update([
            'agentes_id' => json_encode($request->agentes_id),
            // 'nivel_amenaza' => $request->nivel_amenaza,
            'tipo_servicio' => $request->tipo_servicio,
            'ubicacion' => $ubicacionesProcesadas,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'cliente' => $request->cliente,
            // 'nombre_clave' => $request->nombre_clave,
            // 'pasajeros' => $request->pasajeros,
            // 'tipo_operacion' => $request->tipo_operacion,
            'num_vehiculos' => $request->num_vehiculos,
            'tipo_vehiculos' => json_encode($request->tipo_vehiculos ?? []),
            'armados' => $request->armados,

            // ✅ Cambiado: usar 'hoteles' y 'aeropuertos'
            'datos_hotel' => json_encode($request->input('hoteles', [])),
            'datos_aeropuerto' => json_encode($request->input('aeropuertos', [])),

            // ✅ Cambiado: usar 'datos_vuelo' en lugar de 'datos_vuelo_llegada' y 'datos_vuelo_salida'
            'datos_vuelo' => json_encode([
                'llegada' => $request->input('vuelo_llegada', []),
                'salida' => $request->input('vuelo_salida', []),
            ]),
            // 'estatus' => $request->estatus, // Opcionalmente actualizar estatus si es necesario
        ]);

        // === SINCRONIZAR GEOFENCES ===
        // 1. Eliminar todas las geocercas antiguas asociadas a esta misión
        $mision->geofences()->delete(); // Asumiendo que tienes una relación definida en el modelo Misiones

        // 2. Crear nuevas geocercas basadas en los datos actualizados del formulario
        foreach ($request->input('hoteles', []) as $hotel) {
            if (!empty($hotel['nombre']) && isset($hotel['latitud']) && isset($hotel['longitud'])) {
                Geofence::create([
                    'mision_id' => $mision->id,
                    'tipo' => 'hotel',
                    'centro' => [
                        'lat' => $hotel['latitud'],
                        'lng' => $hotel['longitud'],
                    ],
                    'radio_km' => 1.000, // O el valor por defecto que uses
                    'nombre_referencia' => $hotel['nombre'],
                ]);
            }
        }

        foreach ($request->input('aeropuertos', []) as $aeropuerto) {
            if (!empty($aeropuerto['nombre']) && isset($aeropuerto['latitud']) && isset($aeropuerto['longitud'])) {
                Geofence::create([
                    'mision_id' => $mision->id,
                    'tipo' => 'aeropuerto',
                    'centro' => [
                        'lat' => $aeropuerto['latitud'],
                        'lng' => $aeropuerto['longitud'],
                    ],
                    'radio_km' => 4.000, // O el valor por defecto que uses
                    'nombre_referencia' => $aeropuerto['nombre'],
                ]);
            }
        }

        /*
        $agentes = User::whereIn('id', $request->agentes_id)->get();
        $pdf = Pdf::loadView('pdf.mision', [
            'mision' => $mision,
            'agentes' => $agentes,
        ])->setPaper('a4', 'landscape');

        $rutaRelativa = "misiones/{$mision->id}/archivo_mision.pdf";
        Storage::makeDirectory("misiones/{$mision->id}"); // Asegurarse que exista el directorio
        Storage::put($rutaRelativa, $pdf->output());

        $mision->arch_mision = $rutaRelativa; // Actualizar ruta en el modelo
        $mision->save(); // Guardar la ruta del archivo
        */

    } catch (\Exception $e) {
        Log::error('Error al actualizar misión:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'ubicaciones' => $ubicacionesProcesadas,
        ]);
        return back()->withInput()->with('error', 'Ocurrió un error al actualizar la misión.');
    }

    Log::info('Misión actualizada exitosamente', ['id' => $mision->id]);

    return redirect()->route('dashboard')->with('success', 'Misión actualizada exitosamente.');
}

}
