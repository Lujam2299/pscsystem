<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geofences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mision_id');
            $table->string('tipo', 50);
            $table->json('centro');
            $table->decimal('radio_km', 8, 3);
            $table->string('nombre_referencia')->nullable();
            $table->timestamps();

            $table->foreign('mision_id')->references('id')->on('misiones')->onDelete('cascade');
            // Opcional: Índice compuesto para búsquedas eficientes
            // $table->index(['mision_id', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geofences');
    }
};
