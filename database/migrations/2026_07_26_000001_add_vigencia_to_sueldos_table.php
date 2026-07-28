<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sueldos', function (Blueprint $table) {
            $table->date('vigente_desde')->nullable()->after('sueldo_mensual');
            $table->date('vigente_hasta')->nullable()->after('vigente_desde');
            $table->index(['punto', 'puesto', 'vigente_desde', 'vigente_hasta'], 'sueldos_vigencia_idx');
        });
    }

    public function down(): void
    {
        Schema::table('sueldos', function (Blueprint $table) {
            $table->dropIndex('sueldos_vigencia_idx');
            $table->dropColumn(['vigente_desde', 'vigente_hasta']);
        });
    }
};
