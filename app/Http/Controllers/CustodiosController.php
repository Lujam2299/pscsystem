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
            // 'nivel_amenaza' => 'nullable|string|max:255', // REMOVIDO
            'tipo_servicio' => 'required|string|max:255',
            'ubicaciones' => 'required|array|min:1',
            'ubicaciones.*.direccion' => 'nullable|string|max:500',
            'ubicaciones.*.latitud' => 'nullable|numeric',
            'ubicaciones.*.longitud' => 'nullable|numeric',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'cliente' => 'nullable|string|max:255',
            // 'nombre_clave' => 'nullable|string|max:255', // REMOVIDO
            // 'pasajeros' => 'nullable|string|max:255', // REMOVIDO
            // 'tipo_operacion' => 'nullable|string|max:255', // REMOVIDO
            'num_vehiculos' => 'nullable|integer|min:0',
            'tipo_vehiculos' => 'nullable|array',
            'tipo_vehiculos.*' => 'string|max:255',
            'armados' => 'nullable|string|in:armado,desarmado',

            // Validación para Hotel (ahora incluye latitud y longitud)
            'hotel.nombre' => 'nullable|string|max:255',
            'hotel.latitud' => 'nullable|numeric', // Nuevo campo
            'hotel.longitud' => 'nullable|numeric', // Nuevo campo

            // Validación para Aeropuerto (ahora incluye latitud y longitud)
            'aeropuerto.nombre' => 'nullable|string|max:255',
            'aeropuerto.latitud' => 'nullable|numeric', // Nuevo campo
            'aeropuerto.longitud' => 'nullable|numeric', // Nuevo campo

            // Validación para vuelo de llegada (anterior 'vuelo')
            'vuelo_llegada.fecha' => 'nullable|date',
            'vuelo_llegada.flight' => 'nullable|string|max:50',
            'vuelo_llegada.hora' => 'nullable|date_format:H:i',

            // Validación para vuelo de salida (nuevo)
            'vuelo_salida.fecha' => 'nullable|date',
            'vuelo_salida.flight' => 'nullable|string|max:50',
            'vuelo_salida.hora' => 'nullable|date_format:H:i',
        ]);

        $ubicacionesProcesadas = [];

        foreach ($request->ubicaciones as $index => $ubicacion) {
            $direccion = $ubicacion['direccion'];
            $lat = $ubicacion['latitud'] ?? null;
            $lng = $ubicacion['longitud'] ?? null;

            // Si hay dirección pero no coordenadas, intentar geocodificar (respaldo)
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
                        // Opcional: Devolver error o continuar sin coords
                        // return back()->withInput()->with('error', "No se pudo encontrar la dirección: $direccion");
                    }
                } catch (\Exception $e) {
                    Log::error("Error geocodificando dirección #$index", [
                        'direccion' => $direccion,
                        'message' => $e->getMessage(),
                    ]);
                    // Opcional: Devolver error o continuar sin coords
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
                'agentes_id' => json_encode($request->agentes_id),
                // 'nivel_amenaza' => $request->nivel_amenaza, // REMOVIDO
                'tipo_servicio' => $request->tipo_servicio,
                'ubicacion' => $ubicacionesProcesadas,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'cliente' => $request->cliente,
                // 'nombre_clave' => $request->nombre_clave, // REMOVIDO
                // 'pasajeros' => $request->pasajeros, // REMOVIDO
                // 'tipo_operacion' => $request->tipo_operacion, // REMOVIDO
                'num_vehiculos' => $request->num_vehiculos,
                'tipo_vehiculos' => json_encode($request->tipo_vehiculos ?? []),
                'armados' => $request->armados,

                // Datos simplificados actualizados
                // Ahora hotel y aeropuerto son arrays completos que incluyen nombre, latitud y longitud
                'datos_hotel' => json_encode($request->input('hotel', [])), // Esto incluirá 'nombre', 'latitud', 'longitud'
                'datos_aeropuerto' => json_encode($request->input('aeropuerto', [])), // Esto incluirá 'nombre', 'latitud', 'longitud'
                // Guardamos ambos vuelos como JSON
                'datos_vuelo_llegada' => json_encode($request->input('vuelo_llegada', [])), // Anterior 'datos_vuelo'
                'datos_vuelo_salida' => json_encode($request->input('vuelo_salida', [])), // Nuevo campo
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
