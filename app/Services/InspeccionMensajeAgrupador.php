<?php

namespace App\Services;

use App\Models\InspeccionRevisionCaso;
use App\Models\Unidades;

final class InspeccionMensajeAgrupador
{
    public function analizar(InspeccionRevisionCaso $caso): InspeccionRevisionCaso
    {
        $texto = $caso->mensajes()
            ->where('incluido', true)
            ->pluck('texto')
            ->filter()
            ->implode(' ');
        $textoNormalizado = $this->normalizar($texto);

        $candidatas = Unidades::query()
            ->whereNotNull('placas')
            ->get(['id', 'placas', 'marca', 'modelo'])
            ->filter(function (Unidades $unidad) use ($textoNormalizado): bool {
                $placa = $this->normalizar((string) $unidad->placas);

                return strlen($placa) >= 5 && str_contains($textoNormalizado, $placa);
            })
            ->unique('id')
            ->values();

        $estado = match (true) {
            $candidatas->count() === 1 => 'placa_sugerida',
            $candidatas->count() > 1 => 'ambiguo',
            default => 'pendiente',
        };

        $caso->update([
            'estado' => in_array($caso->estado, ['confirmado', 'descartado'], true) ? $caso->estado : $estado,
            'unidad_sugerida_id' => $candidatas->count() === 1 ? $candidatas->first()->id : null,
            'placas_candidatas' => $candidatas->map(fn (Unidades $unidad): array => [
                'unidad_id' => $unidad->id,
                'placa' => $unidad->placas,
                'descripcion' => trim($unidad->marca.' '.$unidad->modelo),
            ])->all(),
            'confianza' => $candidatas->count() === 1 ? 80 : 0,
        ]);

        return $caso->refresh();
    }

    private function normalizar(string $valor): string
    {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $valor));
    }
}
