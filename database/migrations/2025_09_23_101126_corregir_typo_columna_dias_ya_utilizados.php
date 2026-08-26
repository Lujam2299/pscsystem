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
        if (
            Schema::hasColumn('solicitud_vacaciones', 'dias_ya_utlizados')
            && ! Schema::hasColumn('solicitud_vacaciones', 'dias_ya_utilizados')
        ) {
            Schema::table('solicitud_vacaciones', function (Blueprint $table): void {
                $table->renameColumn('dias_ya_utlizados', 'dias_ya_utilizados');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasColumn('solicitud_vacaciones', 'dias_ya_utilizados')
            && ! Schema::hasColumn('solicitud_vacaciones', 'dias_ya_utlizados')
        ) {
            Schema::table('solicitud_vacaciones', function (Blueprint $table): void {
                $table->renameColumn('dias_ya_utilizados', 'dias_ya_utlizados');
            });
        }
    }
};
