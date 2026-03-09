<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sueldos', function (Blueprint $table) {
            $table->id();
            $table->string('punto');
            $table->string('puesto');
            $table->decimal('sd', 10, 2); // Sueldo Diario
            $table->decimal('sdi', 10, 2); // Sueldo Diario Integrado
            $table->decimal('compensacion', 10, 2);
            $table->decimal('nomina_quincenal', 10, 2);
            $table->decimal('sueldo_quincenal', 10, 2);
            $table->decimal('sueldo_mensual', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sueldos');
    }
};
