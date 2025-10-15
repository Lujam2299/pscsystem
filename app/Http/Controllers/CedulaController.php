<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cedula;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class CedulaController extends Controller
{
    public function form()
    {
        $now = Carbon::now();
        $mesActual = $now->copy()->startOfMonth()->format('Y-m-01');
        $anio = $now->year;
        $bimestre = ceil($now->month / 2);
        $periodoBimestral = "{$anio}-B{$bimestre}";

        $emaSubida = Cedula::where('mes_ema', $mesActual)->exists();
        $evaSubida = Cedula::where('periodo_eva', $periodoBimestral)->exists();

        return view('auxadmin.cedulasForm', compact('emaSubida', 'evaSubida'));
    }

    public function upload(Request $req, $tipo)
    {
        $now = now();
        $anio = $now->year;
        $mesEma = $now->copy()->startOfMonth()->format('Y-m-01');
        $bimestre = ceil($now->month / 2);
        $periodoEva = "{$anio}-B{$bimestre}";

        // Validación
        if ($tipo === 'ema') {
            if (Cedula::where('mes_ema', $mesEma)->exists()) {
                return back()->with('error', 'Ya has subido archivos EMA este mes.');
            }
        }

        // Validación
        if ($tipo === 'eva') {
            if (Cedula::where('periodo_eva', $periodoEva)->exists()) {
                return back()->with('error', 'Ya has subido archivos EVA este bimestre.');
            }
        }


        $record = new Cedula();

        // Subir archivos EMA
        if ($tipo === 'ema') {
            foreach (['spyt', 'psc', 'montana'] as $e) {
                if ($req->hasFile("ema_{$e}")) {
                    $file = $req->file("ema_{$e}");
                    $name = "EMA_{$e}_{$mesEma}.pdf";
                    $path = "cedulas/ema/{$anio}/{$mesEma}";
                    $file->storeAs($path, $name, 'public');
                    $record["ema_{$e}"] = "storage/{$path}/{$name}";
                }
            }
            $record->mes_ema = $mesEma;
        }

        // Subir archivos EVA
        if ($tipo === 'eva') {
            foreach (['spyt', 'psc', 'montana'] as $e) {
                if ($req->hasFile("eva_{$e}")) {
                    $file = $req->file("eva_{$e}");
                    $name = "EVA_{$e}_{$periodoEva}.pdf";
                    $path = "cedulas/eva/{$anio}/{$periodoEva}";
                    $file->storeAs($path, $name, 'public');
                    $record["eva_{$e}"] = "storage/{$path}/{$name}";
                }
            }
            $record->periodo_eva = $periodoEva;
        }

        $record->save();

        return back()->with('success', 'Cédulas cargadas correctamente.');
    }
}
