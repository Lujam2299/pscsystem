<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finiquitos', function (Blueprint $table) {
            $table->decimal('salario_diario', 12, 2)->nullable()->after('monto');
            $table->json('desglose')->nullable()->after('salario_diario');
            $table->string('version_formula', 20)->nullable()->after('desglose');
            $table->foreignId('calculado_por')->nullable()->after('version_formula')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('calculado_en')->nullable()->after('calculado_por');
        });
    }

    public function down(): void
    {
        Schema::table('finiquitos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('calculado_por');
            $table->dropColumn(['salario_diario', 'desglose', 'version_formula', 'calculado_en']);
        });
    }
};
