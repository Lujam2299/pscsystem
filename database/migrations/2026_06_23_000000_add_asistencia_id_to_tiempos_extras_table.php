<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tiempos_extras', function (Blueprint $table) {
            $table->foreignId('asistencia_id')
                ->nullable()
                ->after('id')
                ->constrained('asistencias')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tiempos_extras', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asistencia_id');
        });
    }
};
