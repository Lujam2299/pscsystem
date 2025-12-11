<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('misiones', function (Blueprint $table) {
            $table->date('fecha_fin')->nullable()->change();
            $table->string('tipo_servicio')->nullable()->change();
            $table->json('agentes_id')->nullable()->change();
            $table->string('nivel_amenaza')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('misiones', function (Blueprint $table) {
            $table->date('fecha_fin')->nullable(false)->change();
            $table->string('tipo_servicio')->nullable(false)->change();
            $table->json('agentes_id')->nullable(false)->change();
            $table->string('nivel_amenaza')->nullable(false)->change();
        });
    }
};
