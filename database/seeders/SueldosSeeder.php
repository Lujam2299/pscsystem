<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sueldo;
use Illuminate\Support\Facades\File;

class SueldosSeeder extends Seeder
{
    public function run()
    {
        // Ruta al archivo JSON
        $jsonPath = storage_path('app/data/sueldos.json');

        if (!File::exists($jsonPath)) {
            $this->command->info("Archivo JSON no encontrado en: {$jsonPath}");
            return;
        }

        $data = json_decode(File::get($jsonPath), true);

        foreach ($data as $item) {
            Sueldo::create($item);
        }

        $this->command->info('Se han insertado ' . count($data) . ' registros en la tabla sueldos.');
    }
}
