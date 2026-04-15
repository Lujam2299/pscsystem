<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tiempos_extra_costos', function (Blueprint $table) {
            $table->id();
            $table->string('zona'); // MONTERREY, GUANAJUATO, etc.
            $table->decimal('costo_12_horas', 10, 2);
            $table->unique('zona');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tiempos_extra_costos');
    }
};
