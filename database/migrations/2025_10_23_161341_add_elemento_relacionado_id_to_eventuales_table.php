<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('eventuales', function (Blueprint $table) {
            $table->unsignedBigInteger('elemento_relacionado_id')->nullable()->after('motivo');

            $table->foreign('elemento_relacionado_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('eventuales', function (Blueprint $table) {
            $table->dropForeign(['elemento_relacionado_id']);
            $table->dropColumn('elemento_relacionado_id');
        });
    }
};
