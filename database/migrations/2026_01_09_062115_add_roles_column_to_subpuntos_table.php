<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subpuntos', function (Blueprint $table) {
            // Agregamos la columna JSON para guardar el array de roles
            $table->json('roles')->nullable()->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('subpuntos', function (Blueprint $table) {
            $table->dropColumn('roles');
        });
    }
};
