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
        Schema::table('documentacion_altas', function (Blueprint $table) {
            $table->string('arch_acuse_alta')->nullable();
        });
    }

    public function down()
    {
        Schema::table('documentacion_altas', function (Blueprint $table) {
            $table->dropColumn('arch_acuse_alta');
        });
    }
};
