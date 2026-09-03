<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('solicitud_altas', 'zona_supervisor')) {
            Schema::table('solicitud_altas', function (Blueprint $table): void {
                $table->string('zona_supervisor')->nullable();
            });
        }
    }

    public function down(): void
    {
        // The column may predate this deployment and contain production data.
        throw new RuntimeException('La zona se conserva: su eliminación requiere una revisión explícita de datos.');
    }
};
