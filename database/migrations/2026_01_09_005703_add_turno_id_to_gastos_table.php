<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->unsignedBigInteger('Turno_id')->nullable()->after('User_id');
            $table->foreign('Turno_id')->references('id')->on('turno')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->dropForeign(['Turno_id']);
            $table->dropColumn('Turno_id');
        });
    }
};
