<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->decimal('Litros', 10, 2)->after('Monto')->nullable();
        });

        // Hacer campos nullable
        Schema::table('gastos', function (Blueprint $table) {
            $table->string('Tipo')->nullable()->change();
            $table->string('Evidencia')->nullable()->change();
            $table->time('Hora')->nullable()->change();
            $table->date('Fecha')->nullable()->change();
            $table->decimal('Monto', 10, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->dropColumn('Litros');
            $table->string('Tipo')->change(); // revertir a NOT NULL si era así
            $table->string('Evidencia')->change();
            $table->time('Hora')->change();
            $table->date('Fecha')->change();
            $table->decimal('Monto', 10, 2)->change();
        });
    }
};
