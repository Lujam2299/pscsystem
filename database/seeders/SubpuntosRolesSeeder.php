<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subpunto;

class SubpuntosRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Mapeo: 'Nombre Exacto en BD' => ['Lista', 'De', 'Roles']
        $configuracion = [
            'AMERICAN AIRLINES' => ['GUARDIA'],
            'BONETERA' => ['GUARDIA'],
            'DALTILE' => ['GUARDIA', 'MONITORISTA ENCARGADA', 'MONITORISTA', 'RECEPCIONISTA'],
            'GOBAR' => ['GUARDIA'],
            'HOMEDEPOT' => ['COORDINADOR', 'GUARDIA 1', 'GUARDIA 2', 'GUARDIA NOCHE'],
            'OFF ON GREEN' => ['GUARDIA'],
            'MTY' => ['SUPERVISOR', 'APOYO SUPERVISOR', 'K9', 'CORTADOR', 'GUARDIA', 'RECEPCIONISTA'],
            'KSOC' => ['GUARDIA'],
            'KINDER MORGAN' => ['GUARDIA'],
            'OATEY' => ['GUARDIA', 'ENCARGADO'],
            'MARYKAY CORPORATIVO' => ['ENCARGADO CORPORATIVO', 'GUARDIA'],
            'PEMCORP #2' => ['GUARDIA'],
            'SACMI DE MEXICO' => ['GUARDIA'],
            'THERMO ELECTRICA' => ['SUPERVISOR', 'MOVIL', 'JT', 'GUARDIA'],

            'TORRE DELTA' => ['GUARDIA', 'SUPERVISOR'],
            'TORRENOVO' => ['GUARDIA'],
            // 'SUPERVISOR' como punto lo dejo comentado, revísalo en tu BD si es un nombre real o error
            // 'SUPERVISOR' => ['MONTERREY'],
            'COOPER LIGHT' => ['GUARDIA'],
            'MONTE PALATINO' => ['GUARDIA'],
            'PLAZA DOMENA' => ['GUARDIA'],
            'ROCHE BOBOIS' => ['GUARDIA'],
            'INTERPUERTO Y TALLER' => ['GUARDIA', 'JEFE DE TURNO', 'ENCARGADO', 'RONDINERO'],
            'WATCO' => ['GUARDIA', 'JEFE DE TURNO', 'ENCARGADO'],
            'BMW' => ['GUARDIA', 'MOVIL'],

            // SLP aparece dos veces en tu lista con distintos roles, los unimos en un solo array
            'SLP' => ['ASISTENTE DE RH', 'AGENTE DE SEGURIDAD'],

            'ZONA DE ABASTOS I' => ['JEFE DE SERVICIOS'],
            'VALLE DE MEXICO' => ['ENCARGADO', 'ASISTENTE', 'JEFE DE TURNO', 'PATRULLERO', 'PATRULLERO LECHERIA', 'R.O', 'GUARDIA'],
            'TOLUCA' => ['ENCARGADO', 'JEFE DE TURNO', 'PATRULLERO', 'R.O', 'GUARDIA'],
            'SILAO' => ['ENCARGADO', 'ASISTENTE', 'JEFE DE TURNO', 'PATRULLERO', 'GUARDIA'],
            'SALAMANCA' => ['ENCARGADO', 'ASISTENTE', 'JEFE DE TURNO', 'PATRULLERO', 'GUARDIA'],
            'CELAYA' => ['ENCARGADO', 'ASISTENTE', 'JEFE DE TURNO', 'PATRULLERO', 'GUARDIA'],
        ];

        foreach ($configuracion as $nombrePunto => $roles) {
            // Buscamos únicamente por NOMBRE, ya que es el identificador único humano
            $subpunto = Subpunto::where('nombre', $nombrePunto)->first();

            if ($subpunto) {
                $subpunto->update(['roles' => $roles]);
                $this->command->info("✅ Actualizado: {$subpunto->nombre} (Cód: {$subpunto->codigo}) con roles: " . implode(', ', $roles));
            } else {
                $this->command->warn("⚠️ No se encontró el punto con nombre: '{$nombrePunto}'. Verifica mayúsculas/minúsculas en la BD.");
            }
        }
    }
}
