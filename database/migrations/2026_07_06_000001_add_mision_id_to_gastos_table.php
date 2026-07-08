<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gastos', function (Blueprint $table): void {
            // Nullable conserva los registros históricos; los gastos nuevos
            // enviados por la app siempre incluirán una misión válida.
            $table->foreignId('mision_id')
                ->nullable()
                ->after('id')
                ->constrained('misiones')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gastos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('mision_id');
        });
    }
};
