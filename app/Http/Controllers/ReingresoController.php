<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reingreso;
use App\Models\SolicitudAlta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReingresoController extends Controller
{

    public function procesarReingresos()
    {
        // Usamos 'with' para eager loading de la relación solicitudAlta para rendimiento
        $usuarios = User::where('id', '!=', 1)
            ->has('solicitudAlta')
            ->get();

        $registrosCreados = 0;

        foreach ($usuarios as $usuario) {
            $solicitud = $usuario->solicitudAlta;

            if (!$solicitud) {
                continue;
            }

            $valorReingreso = trim($solicitud->reingreso ?? '');

            if (empty($valorReingreso) ||
                strtoupper($valorReingreso) === 'NO' ||
                strtoupper($valorReingreso) === 'SI') {
                continue;
            }

            $fechasEncontradas = $this->extraerFechasDeTexto($valorReingreso);

            if (empty($fechasEncontradas)) {
                continue;
            }

            sort($fechasEncontradas);

            foreach ($fechasEncontradas as $index => $fechaObj) {
                $existe = Reingreso::where('user_id', $usuario->id)
                    ->where('fecha', $fechaObj->format('Y-m-d'))
                    ->exists();

                if (!$existe) {
                    Reingreso::create([
                        'user_id' => $usuario->id,
                        'numero_reingreso' => $index + 1,
                        'fecha' => $fechaObj->format('Y-m-d'),
                    ]);
                    $registrosCreados++;
                }
            }
        }

        return response()->json([
            'mensaje' => 'Proceso completado exitosamente.',
            'registros_creados' => $registrosCreados
        ]);
    }

    private function extraerFechasDeTexto(string $texto): array
    {
        $fechas = [];

        $patron = '/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/';

        if (preg_match_all($patron, $texto, $coincidencias, PREG_SET_ORDER)) {
            foreach ($coincidencias as $coincidencia) {
                $dia = (int)$coincidencia[1];
                $mes = (int)$coincidencia[2];
                $anio = (int)$coincidencia[3];

                if ($mes >= 1 && $mes <= 12 && $dia >= 1 && $dia <= 31) {
                    try {
                        $fechaCarbon = Carbon::createFromDate($anio, $mes, $dia);

                        if ($fechaCarbon->day == $dia && $fechaCarbon->month == $mes) {
                            $fechas[] = $fechaCarbon;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        return $fechas;
    }
}
