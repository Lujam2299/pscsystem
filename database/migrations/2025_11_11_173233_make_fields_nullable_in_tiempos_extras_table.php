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
        Schema::table('tiempos_extras', function (Blueprint $table) {
            $table->string('hora_inicio')->nullable()->change();
            $table->string('hora_fin')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tiempos_extras', function (Blueprint $table) {
            $table->string('hora_inicio')->change();
            $table->string('hora_fin')->change();
        });
    }
};
