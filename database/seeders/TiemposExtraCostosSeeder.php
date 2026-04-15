<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TiempoExtraCosto;

class TiemposExtraCostosSeeder extends Seeder
{
    public function run()
    {
        TiempoExtraCosto::insert([
            ['zona' => 'VALLE DE MEXICO', 'costo_12_horas' => 400.00],
            ['zona' => 'GUANAJUATO', 'costo_12_horas' => 470.00],
            ['zona' => 'PUEBLA', 'costo_12_horas' => 400.00],
            ['zona' => 'TOLUCA', 'costo_12_horas' => 400.00],
            ['zona' => 'MONTERREY', 'costo_12_horas' => 450.00],
            ['zona' => 'SLP', 'costo_12_horas' => 400.00],
        ]);
    }
}
