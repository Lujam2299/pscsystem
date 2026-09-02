<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitud_altas', function (Blueprint $table) {
            if (! Schema::hasColumn('solicitud_altas', 'zona_supervisor')) {
                $table->string('zona_supervisor')->nullable()->after('punto');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitud_altas', function (Blueprint $table) {
            if (Schema::hasColumn('solicitud_altas', 'zona_supervisor')) {
                $table->dropColumn('zona_supervisor');
            }
        });
    }
};
