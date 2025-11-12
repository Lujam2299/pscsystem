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
        Schema::create('permiso_especials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('tipo');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('con_goce')->default(false);
            $table->text('motivo')->nullable();
            $table->string('archivo_justificante')->nullable();
            $table->foreignId('registrado_por')->constrained('users');
            $table->string('estatus', 20)->default('Pendiente');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('permiso_especials');
    }
};
