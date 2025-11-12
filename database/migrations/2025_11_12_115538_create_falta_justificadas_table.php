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
        Schema::create('falta_justificadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asistencia_id')->nullable()->constrained('asistencias')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users');
            $table->date('fecha');
            $table->string('tipo', 20); // 'justificada', 'injustificada'
            $table->text('motivo')->nullable();
            $table->string('archivo_justificante')->nullable();
            $table->foreignId('registrado_por')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('falta_justificadas');
    }
};
