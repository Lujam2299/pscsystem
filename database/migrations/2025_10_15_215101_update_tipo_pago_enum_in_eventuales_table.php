<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('eventuales', function (Blueprint $table) {
        // Cambiar ENUM para incluir 'eventual'
        $table->enum('tipo_pago', ['nomina', 'efectivo', 'eventual'])->change();
    });
}

public function down()
{
    Schema::table('eventuales', function (Blueprint $table) {
        // Volver al ENUM anterior (opcional: cuidado con datos existentes)
        $table->enum('tipo_pago', ['nomina', 'efectivo'])->change();
    });
}
};
