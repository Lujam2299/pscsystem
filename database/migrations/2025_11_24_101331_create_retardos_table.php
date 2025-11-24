<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('retardos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('asistencia_id')->nullable()->constrained('asistencias')->onDelete('set null');
            $table->date('fecha');
            $table->integer('minutos_retardo'); // en minutos
            $table->foreignId('registrado_por')->constrained('users');
            $table->timestamps();

            $table->index(['user_id', 'fecha']); // Para consultas rápidas
        });
    }

    public function down()
    {
        Schema::dropIfExists('retardos');
    }
};
