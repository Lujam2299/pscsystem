<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspecciones_unidades', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('unidad_id')->constrained('unidades')->restrictOnDelete();
            $table->dateTime('fecha_inspeccion');
            $table->string('tipo', 40)->default('cambio_turno');
            $table->unsignedBigInteger('kilometraje')->nullable();
            $table->string('resultado', 40)->default('sin_novedad');
            $table->text('observaciones')->nullable();
            $table->string('reportado_por')->nullable();
            $table->string('origen', 30)->default('manual');
            $table->string('estado', 30)->default('validada');
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();
            $table->foreignId('siniestro_id')->nullable()->constrained('siniestros')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['unidad_id', 'fecha_inspeccion']);
            $table->index(['resultado', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspecciones_unidades');
    }
};
