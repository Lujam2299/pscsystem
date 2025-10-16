<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('riesgo_trabajos', function (Blueprint $table) {
            $table->date('fecha')->nullable()->after('descripcion_observaciones');
            $table->string('folio', 100)->nullable()->after('fecha');
        });
    }

    public function down()
    {
        Schema::table('riesgo_trabajos', function (Blueprint $table) {
            $table->dropColumn(['fecha', 'folio']);
        });
    }
};
