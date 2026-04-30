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
        Schema::table('solicitud_altas', function (Blueprint $table) {
            $table->string('tipo_cuenta_bancaria')->nullable()->after('sueldo_mensual');
            $table->string('banco')->nullable()->after('tipo_cuenta_bancaria');
            $table->string('cuenta_bancaria')->nullable()->after('banco');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitud_altas', function (Blueprint $table) {
            $table->dropColumn(['tipo_cuenta_bancaria', 'banco', 'cuenta_bancaria']);
        });
    }
};
