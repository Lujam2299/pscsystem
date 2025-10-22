<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('eventuales', function (Blueprint $table) {
            $table->string('tipo_servicio')->nullable(); // '12 Horas', '24 horas', '36 Horas'
            $table->string('motivo')->nullable();       // 'Falta de plantilla', etc.
        });
    }

    public function down()
    {
        Schema::table('eventuales', function (Blueprint $table) {
            $table->dropColumn(['tipo_servicio', 'motivo']);
        });
    }
};
