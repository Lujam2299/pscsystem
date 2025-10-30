<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('asistencia_puntos', function (Blueprint $table) {
            $table->string('turno')->nullable()->after('punto');
        });
    }

    public function down()
    {
        Schema::table('asistencia_puntos', function (Blueprint $table) {
            $table->dropColumn('turno');
        });
    }
};
