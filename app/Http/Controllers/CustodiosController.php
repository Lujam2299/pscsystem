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

    public function custodiosIndex(){
        $agentes = User::where('estatus', 'Activo')
            ->whereRaw("LOWER(rol) LIKE ?", ['%escolta%'])
            ->get();

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

        'hotel.nombre' => 'nullable|string|max:255',
        'hotel.latitud' => 'nullable|numeric',
        'hotel.longitud' => 'nullable|numeric',

        'aeropuerto.nombre' => 'nullable|string|max:255',
        'aeropuerto.latitud' => 'nullable|numeric',
        'aeropuerto.longitud' => 'nullable|numeric',

        'vuelo_llegada.fecha' => 'nullable|date',
        'vuelo_llegada.flight' => 'nullable|string|max:50',
        'vuelo_llegada.hora' => 'nullable|date_format:H:i',

        'vuelo_salida.fecha' => 'nullable|date',
        'vuelo_salida.flight' => 'nullable|string|max:50',
        'vuelo_salida.hora' => 'nullable|date_format:H:i',
    ]);

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
                    // return back()->withInput()->with('error', "No se pudo encontrar la dirección: $direccion");
                }
            } catch (\Exception $e) {
                Log::error("Error geocodificando dirección #$index", [
                    'direccion' => $direccion,
                    'message' => $e->getMessage(),
                ]);
                // return back()->withInput()->with('error', "Error geocodificando la dirección: $direccion");
            }
        }

        $ubicacionesProcesadas[] = [
            'direccion' => $direccion,
            'latitud' => $lat,
            'longitud' => $lng,
        ];
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

            'datos_hotel' => json_encode($request->input('hotel', [])),
            'datos_aeropuerto' => json_encode($request->input('aeropuerto', [])),
            'datos_vuelo_llegada' => json_encode($request->input('vuelo_llegada', [])),
            'datos_vuelo_salida' => json_encode($request->input('vuelo_salida', [])),
            'estatus' => 'Pendiente',
        ]);
    } catch (\Exception $e) {
        Log::error('Error al guardar misión:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'ubicaciones' => $ubicacionesProcesadas,
        ]);
        return back()->withInput()->with('error', 'Ocurrió un error al guardar la misión.');
    }

    if ($request->filled('hotel.nombre') && $request->filled('hotel.latitud') && $request->filled('hotel.longitud')) {
        Geofence::create([
            'mision_id' => $mision->id,
            'tipo' => 'hotel',
            'centro' => [
                'lat' => $request->hotel['latitud'],
                'lng' => $request->hotel['longitud'],
            ],
            'radio_km' => 3.000,
            'nombre_referencia' => $request->hotel['nombre'],
        ]);
    }

    if ($request->filled('aeropuerto.nombre') && $request->filled('aeropuerto.latitud') && $request->filled('aeropuerto.longitud')) {
        Geofence::create([
            'mision_id' => $mision->id,
            'tipo' => 'aeropuerto',
            'centro' => [
                'lat' => $request->aeropuerto['latitud'],
                'lng' => $request->aeropuerto['longitud'],
            ],
            'radio_km' => 7.000,
            'nombre_referencia' => $request->aeropuerto['nombre'],
        ]);
    }
    // --- Fin Crear Geocercas ---

    // --- COMENTADO: Generación y guardado del PDF ---
    /*
    $agentes = User::whereIn('id', $request->agentes_id)->get();

    $pdf = Pdf::loadView('pdf.mision', [
        'mision' => $mision,
        'agentes' => $agentes,
    ])->setPaper('a4', 'landscape');

    $rutaRelativa = "misiones/{$mision->id}/archivo_mision.pdf";
    Storage::makeDirectory("misiones/{$mision->id}");
    Storage::put($rutaRelativa, $pdf->output());

    $mision->arch_mision = $rutaRelativa;
    $mision->save();
    */

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

}
