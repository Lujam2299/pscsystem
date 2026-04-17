<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('isr_tarifas', function (Blueprint $table) {
            $table->id();
            $table->integer('anio');
            $table->decimal('limite_inferior', 15, 2);
            $table->decimal('limite_superior', 15, 2);
            $table->decimal('cuota_fija', 15, 2);
            $table->decimal('porcentaje_excedente', 7, 4);
            $table->timestamps();

            $table->index(['anio', 'limite_inferior']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('isr_tarifas');
    }
};
